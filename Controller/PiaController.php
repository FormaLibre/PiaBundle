<?php
namespace Laurent\PiaBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Laurent\PiaBundle\Entity\Suivis;
use Laurent\PiaBundle\Entity\Constat;
use Laurent\PiaBundle\Entity\Taches;
use Laurent\PiaBundle\Form\SuiviType;
use Laurent\PiaBundle\Form\ConstatType;
use Laurent\BulletinBundle\Manager\TotauxManager;

class PiaController extends Controller
{
    private $authorization;
    private $om;
    /** @var tachesRepository */
    private $tachesRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var actionsRepository */
    private $actionsRepo;
    private $constatRepo;
    private $classeRepo;
    private $userRepo;
    private $totauxManager;
    private $formFactory;
    private $request;

    /**
     * @DI\InjectParams({
     *      "authorization"      = @DI\Inject("security.authorization_checker"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "totauxManager"      = @DI\Inject("laurent.manager.totaux_manager"),
     *      "formFactory"        = @DI\Inject("form.factory"),
     *      "requestStack"       = @DI\Inject("request_stack")
     * })
     */

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        TotauxManager $totauxManager,
        FormFactory $formFactory,
        RequestStack $requestStack
    )
    {
        $this->authorization      = $authorization;
        $this->om                 = $om;
        $this->tachesRepo         = $om->getRepository('LaurentPiaBundle:Taches');
        $this->suivisRepo         = $om->getRepository('LaurentPiaBundle:Suivis');
        $this->actionsRepo        = $om->getRepository('LaurentPiaBundle:Actions');
        $this->constatRepo        = $om->getRepository('LaurentPiaBundle:Constat');
        $this->classeRepo         = $om->getRepository('LaurentSchoolBundle:Classe');
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User');
        $this->totauxManager      = $totauxManager;
        $this->formFactory        = $formFactory;
        $this->request            = $requestStack->getCurrentRequest();
    }

    /**
     * @EXT\Route("/", name="laurentPiaIndex")
     */
    public function indexAction()
    {
        $classes = $this->classeRepo->findAll();
        foreach ($classes as $classe){
            $groups[] = $classe->getGroup();
        }
        return $this->render('LaurentPiaBundle::PiaIndex.html.twig', array('groups' => $groups));
    }

    /**
     * @EXT\Route("/group/{group}/cdc/", name="laurentPiaCdc")
     *
     * @param Group $group
     *
     */
    public function cdcAction(Group $group)
    {
        $this->checkOpen();
        $eleves = $this->userRepo->findByGroup($group);
        $sorted = array();
        $temp = array();

        foreach ($eleves as $eleve) {
            $indice = $this->calculIndice($eleve);

            if (!isset($temp[$indice])) {
                $temp[$indice] = array();
            }
            $temp[$indice][] = $eleve;
        }
        ksort($temp);
        $temp = array_reverse($temp);

        foreach ($temp as $t) {
            $sorted = array_merge($sorted, $t);
        }

        $params = array('group' => $group, 'eleves' => $sorted);

        return $this->render('LaurentPiaBundle::PiaCdc.html.twig', $params);
    }

    /**
     * @EXT\Route("/user/{user}/fiche/", name="laurentPiaFiche")
     *
     * @param User $user
     *
     */
    public function ficheAction(User $user)
    {
        $this->checkOpen();
        $constats = $this->constatRepo->findByUser($user, array('creationDate' => 'ASC'));

        $params = array('user' => $user, 'constats' => $constats);

        return $this->render('LaurentPiaBundle::PiaFiche.html.twig', $params);
    }

    /**
     * @EXT\Route("/user/{user}/pirWidget/", name="laurentPiaPirWidget")
     *
     * @param User $user
     *
     */
    public function pirWidgetAction(User $user)
    {
        $taches = $this->tachesRepo->findByEleves($user, array('fini'=>'ASC', 'priorite'=>'DESC'));

        //findBy(array('eleves' => $user))

        $params = array('user' => $user, 'taches' => $taches);

        return $this->render('LaurentPiaBundle::PirWidget.html.twig', $params);
    }


    /**
     * @EXT\Route("/tache/{tache}/suivi/", name="laurentPiaSuivi", options = {"expose"=true})
     *
     * @param Taches $tache
     *
     */
    public function suiviAction(Taches $tache, Request $request)
    {
        $this->checkOpen();

        $suivi = $this->suivisRepo->findByTaches($tache);

        $suiviNew = new Suivis();
        $form = $this->createForm(new SuiviType, $suiviNew);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $suiviNew->setTaches($tache);
                $this->om->persist($suiviNew);
                $this->om->flush();
            } else {
                $errors = $form->getErrorsAsString();
                var_dump($errors);
            }

            $user = $tache->getEleves();
            return $this->render('LaurentPiaBundle::PiaFiche.html.twig', array('user' => $user));
        }

        $params = array('tache' => $tache, 'suivis' => $suivi, 'form' =>  $form->createView());

        return $this->render('LaurentPiaBundle::suiviWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/user/{user}/create/form",
     *     name="laurentPiaConstatCreateForm",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function constatCreateFormAction(User $user)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new ConstatType(), new Constat());

        $params = array(
            'form' => $form->createView(),
            'user' => $user
        );

        return $this->render('LaurentPiaBundle::constatCreateModalForm.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/user/{user}/create",
     *     name="laurentPiaConstatCreate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function constatCreateAction(User $user)
    {
        $this->checkOpen();
        $constat = new Constat();
        $constat->setUser($user);
        $form = $this->formFactory->create(new ConstatType(), $constat);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $constat->setCreationDate(new \DateTime());
            $this->om->persist($constat);
            $this->om->flush();
            $datas = array(
                'id' => $constat->getId(),
                'content' => $constat->getContent(),
                'creationDate' => $constat->getCreationDate()->format('d/m/y H:i')
            );

            return new JsonResponse($datas, 200);
        } else {
            $params = array(
                'form' => $form->createView(),
                'user' => $user
            );

            return $this->render('LaurentPiaBundle::constatCreateModalForm.html.twig', $params);
        }
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/edit/form",
     *     name="laurentPiaConstatEditForm",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function constatEditFormAction(Constat $constat)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new ConstatType(), $constat);

        $params = array(
            'form' => $form->createView(),
            'constat' => $constat
        );

        return $this->render('LaurentPiaBundle::constatEditModalForm.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/edit",
     *     name="laurentPiaConstatEdit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function constatEditAction(Constat $constat)
    {
        $this->checkOpen();
        $form = $this->formFactory->create(new ConstatType(), $constat);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $constat->setEditionDate(new \DateTime());
            $this->om->persist($constat);
            $this->om->flush();
            $datas = array(
                'id' => $constat->getId(),
                'content' => $constat->getContent(),
                'creationDate' => $constat->getCreationDate()->format('d/m/y H:i'),
                'editionDate' => $constat->getEditionDate()->format('d/m/y H:i')
            );

            return new JsonResponse($datas, 200);
        } else {
            $params = array(
                'form' => $form->createView(),
                'constat' => $constat
            );

            return $this->render('LaurentPiaBundle::constatEditModalForm.html.twig', $params);
        }
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/delete",
     *     name="laurentPiaConstatDelete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function constatDeleteAction(Constat $constat)
    {
        $this->checkOpen();
        $this->om->remove($constat);
        $this->om->flush();

        return new JsonResponse('success', 200);
    }

    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }


    public function calculIndice(User $user){

        $totauxMatieres = $this->totauxManager->getTotalPeriodes($user);
        $i = 0;

        foreach ($totauxMatieres as $total){
            $i = 100 - $total;
        }
        return $i;
    }

}
