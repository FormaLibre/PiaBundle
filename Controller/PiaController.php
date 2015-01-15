<?php
namespace Laurent\PiaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Persistence\ObjectManager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Laurent\PiaBundle\Entity\Suivis;
use Laurent\PiaBundle\Entity\Actions;
use Laurent\PiaBundle\Entity\Taches;
use Laurent\PiaBundle\Form\TacheType;
use Laurent\PiaBundle\Form\SuiviType;
use Laurent\BulletinBundle\Manager\TotauxManager;

class PiaController extends Controller
{
    private $sc;
    private $em;
    private $om;
    /** @var tachesRepository */
    private $tachesRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var actionsRepository */
    private $actionsRepo;
    private $classeRepo;
    private $userRepo;
    private $totauxManager;

    /**
     * @DI\InjectParams({
     *      "sc"                 = @DI\Inject("security.context"),
     *      "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *      "totauxManager"      = @DI\Inject("laurent.manager.totaux_manager"),
     * })
     */

    public function __construct(
        SecurityContextInterface $sc,
        EntityManager $em,
        ObjectManager $om,
        TotauxManager $totauxManager
    )
    {
        $this->sc                 = $sc;
        $this->em                 = $em;
        $this->om                 = $om;
        $this->tachesRepo         = $om->getRepository('LaurentPiaBundle:Taches');
        $this->suivisRepo         = $om->getRepository('LaurentPiaBundle:Suivis');
        $this->actionsRepo        = $om->getRepository('LaurentPiaBundle:Actions');
        $this->classeRepo          = $om->getRepository('LaurentSchoolBundle:Classe');
        $this->userRepo           = $om->getRepository('ClarolineCoreBundle:User');
        $this->totauxManager      = $totauxManager;
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
        $sorted = [];

        foreach ($eleves as $eleve) {
            $sorted[$this->calculIndice($eleve)] = $eleve;
        }

        ksort($sorted);
        $sorted = array_reverse($sorted);


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

        $params = array('user' => $user);

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
     * @EXT\Route("/tache/add/{user}", name="laurentPiaTacheAdd", options = {"expose"=true})
     *
     * @EXT\Template("LaurentPiaBundle::TacheForm.html.twig")
     */
    public function TacheAddAction(Request $request, User $user)
    {
        $this->checkOpen();
        $taches = new Taches();
        $form = $this->createForm(new TacheType, $taches);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $taches->setEleves($user);
                $this->em->persist($taches);
                $this->em->flush();
            }
        }
        return array('form' => $form->createView(), 'action' => $this->generateUrl('laurentPiaTacheAdd', array('user'=>$user->getId())));
    }

    /**
     * @EXT\Route("/tache/{tache}/close", name="laurentPiaTacheClose", options = {"expose"=true})
     *
     */
    public function TacheCloseAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $tache->setFini(1);
        $this->em->persist($tache);
        $this->em->flush();

        return Response::HTTP_ACCEPTED;
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
                $this->em->persist($suiviNew);
                $this->em->flush();
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

    private function checkOpen()
    {
        if ($this->sc->isGranted('ROLE_PROF')) {
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
