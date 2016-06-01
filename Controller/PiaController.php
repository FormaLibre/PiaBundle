<?php

namespace FormaLibre\PiaBundle\Controller;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Manager\CursusManager;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use FormaLibre\BulletinBundle\Manager\TotauxManager;
use FormaLibre\PiaBundle\Entity\Constat;
use FormaLibre\PiaBundle\Entity\Suivis;
use FormaLibre\PiaBundle\Entity\Taches;
use FormaLibre\PiaBundle\Form\ConstatType;
use FormaLibre\PiaBundle\Form\SuiviType;
use FormaLibre\PiaBundle\Manager\PiaManager;
use FormaLibre\PresenceBundle\Manager\PresenceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PiaController extends Controller
{
    private $authorization;
    private $bulletinManager;
    private $cursusManager;
    private $formFactory;
    private $om;
    /** @var  string */
    private $pdfDir;
    private $piaManager;
    private $presenceManager;
    private $request;
    private $totauxManager;

    /** @var tachesRepository */
    private $tachesRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var actionsRepository */
    private $actionsRepo;
    private $constatRepo;
    private $userRepo;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pdfDir"          = @DI\Inject("%formalibre.directories.pdf%"),
     *     "piaManager"      = @DI\Inject("formalibre.manager.pia_manager"),
     *     "presenceManager" = @DI\Inject("formalibre.manager.presence_manager"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "totauxManager"   = @DI\Inject("formalibre.manager.totaux_manager")
     * })
     */

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BulletinManager $bulletinManager,
        CursusManager $cursusManager,
        FormFactory $formFactory,
        ObjectManager $om,
        $pdfDir,
        PiaManager $piaManager,
        PresenceManager $presenceManager,
        RequestStack $requestStack,
        TotauxManager $totauxManager
    )
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->pdfDir = $pdfDir;
        $this->piaManager = $piaManager;
        $this->presenceManager = $presenceManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->totauxManager = $totauxManager;

        $this->tachesRepo = $om->getRepository('FormaLibrePiaBundle:Taches');
        $this->suivisRepo = $om->getRepository('FormaLibrePiaBundle:Suivis');
        $this->actionsRepo = $om->getRepository('FormaLibrePiaBundle:Actions');
        $this->constatRepo = $om->getRepository('FormaLibrePiaBundle:Constat');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * @EXT\Route("/", name="formalibrePiaIndex")
     */
    public function indexAction()
    {
        $groups = $this->bulletinManager->getTaggedGroups();

        return $this->render(
            'FormaLibrePiaBundle::PiaIndex.html.twig',
            array('groups' => $groups)
        );
    }

    /**
     * @EXT\Route("/group/{group}/cdc/", name="formalibrePiaCdc")
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

        return $this->render('FormaLibrePiaBundle::PiaCdc.html.twig', $params);
    }

    /**
     * @EXT\Route("/user/{user}/fiche/", name="formalibrePiaFiche")
     *
     * @param User $user
     *
     */
    public function ficheAction(User $user)
    {
        $this->checkOpen();
        $constats = $this->constatRepo->findByUser($user, array('creationDate' => 'ASC'));

        $params = array('user' => $user, 'constats' => $constats);

        return $this->render('FormaLibrePiaBundle::PiaFiche.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/printable/fiche/print",
     *     name="formalibrePiaFichePrint"
     * )
     *
     * @param User $user
     *
     */
    public function fichePrintAction(User $user)
    {
        $this->checkOpen();
        $filename = $user->getLastName() . $user->getFirstName(). '-PIA-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . 'PIA/' . $filename;

        $eleveUrl = $this->generateUrl('formalibrePiaPrintableFiche', array('user' => $user->getId()), true);
        $this->get('knp_snappy.pdf')->generate($eleveUrl, $dir);

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
        );

        return new Response(file_get_contents($dir), 200, $headers);
    }

    /**
     * @EXT\Route(
     *     "/group/{group}/printable/fiche/print",
     *     name="formalibrePiaGroupFichePrint",
     *     options = {"expose"=true}
     * )
     *
     * @param Group $group
     *
     */
    public function groupFichePrintAction(Group $group)
    {
        $this->checkOpen();
        $filename = $group->getName(). '-PIA-'. date("Y-m-d-H-i-s") . '.pdf';
        $dir = $this->pdfDir . 'PIA/' . $group->getName() . '/' . $filename;
        $eleves = $this->userRepo->findByGroup($group);
        $elevesUrl = array();

        foreach ($eleves as $eleve){
            $elevesUrl[] = $this->generateUrl('formalibrePiaPrintableFiche', array('user' => $eleve->getId()), true);
        }
        $this->get('knp_snappy.pdf')->generate($elevesUrl, $dir);

        $headers = array(
            'Content-Type'          => 'application/pdf',
            'Content-Disposition'   => 'attachment; filename="'.$filename.'"'
        );

        return new Response(file_get_contents($dir), 200, $headers);
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/printable/fiche/",
     *     name="formalibrePiaPrintableFiche"
     * )
     *
     * @param User $user
     *
     */
    public function fichePrintableVersionAction(Request $request, User $user)
    {
        $this->checkOpenPrintPdf($request);
//        $this->checkOpen();
        $constats = $this->constatRepo->findByUser($user, array('creationDate' => 'ASC'));

        $params = array('user' => $user, 'constats' => $constats);

        return $this->render('FormaLibrePiaBundle::PiaPrintableFiche.html.twig', $params);
    }

    /**
     * @EXT\Route("/user/{user}/pirWidget/", name="formalibrePiaPirWidget")
     *
     * @param User $user
     *
     */
    public function pirWidgetAction(User $user)
    {
        $taches = $this->tachesRepo->findByEleves($user, array('fini'=>'ASC', 'priorite'=>'DESC'));

        //findBy(array('eleves' => $user))

        $params = array('user' => $user, 'taches' => $taches);

        return $this->render('FormaLibrePiaBundle::PirWidget.html.twig', $params);
    }


    /**
     * @EXT\Route("/tache/{tache}/suivi/", name="formalibrePiaSuivi", options = {"expose"=true})
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
            $constats = $this->constatRepo->findByUser($user, array('creationDate' => 'ASC'));
            return $this->render('FormaLibrePiaBundle::PiaFiche.html.twig', array('user' => $user, 'constats' => $constats));
        }

        $params = array('tache' => $tache, 'suivis' => $suivi, 'form' =>  $form->createView());

        return $this->render('FormaLibrePiaBundle::suiviWidget.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/user/{user}/create/form",
     *     name="formalibrePiaConstatCreateForm",
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

        return $this->render('FormaLibrePiaBundle::constatCreateModalForm.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/user/{user}/create",
     *     name="formalibrePiaConstatCreate",
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

            return $this->render('FormaLibrePiaBundle::constatCreateModalForm.html.twig', $params);
        }
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/edit/form",
     *     name="formalibrePiaConstatEditForm",
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

        return $this->render('FormaLibrePiaBundle::constatEditModalForm.html.twig', $params);
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/edit",
     *     name="formalibrePiaConstatEdit",
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

            return $this->render('FormaLibrePiaBundle::constatEditModalForm.html.twig', $params);
        }
    }

    /**
     * @EXT\Route(
     *     "constat/{constat}/delete",
     *     name="formalibrePiaConstatDelete",
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

    /**
     * @EXT\Route(
     *     "pia/user/{user}/facets/widget",
     *     name="formalibre_pia_facets_widget",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibrePiaBundle::piaFacetsWidget.html.twig")
     */
    public function piaFacetsWidgetAction(User $user)
    {
        $this->checkOpen();
        $datas = array();
        $facets = $this->piaManager->getAllFacets();
        $panelFacets = $this->piaManager->getAllPanelFacets();
        $fieldFacets = $this->piaManager->getAllFieldFacets();
        $values = $this->piaManager->getFieldFacetValuesByUser($user);
        $panels = array();
        $fields = array();

        foreach ($panelFacets as $panel) {
            $facetId = $panel->getFacet()->getId();

            if (!isset($panels[$facetId])) {
                $panels[$facetId] = array();
            }
            $panels[$facetId][] = $panel;
        }

        foreach ($fieldFacets as $field) {
            $fieldId = $field->getId();
            $panelId = $field->getPanelFacet()->getId();

            if (!isset($fields[$panelId])) {
                $fields[$panelId] = array();
                $fields[$panelId][$fieldId] = array();
            }
            $fields[$panelId][$fieldId]['name'] = $field->getName();
            $fields[$panelId][$fieldId]['value'] = null;
        }

        foreach ($values as $value) {
            $field = $value->getFieldFacet();
            $fieldId = $field->getId();
            $type = $field->getType();
            $panelId = $value->getFieldFacet()->getPanelFacet()->getId();
            $fieldValue = $value->getValue();

            if ($type === FieldFacet::DATE_TYPE) {
                $fieldValue = $fieldValue->format('Y-m-d H:i');
            }
            $fields[$panelId][$fieldId]['value'] = $fieldValue;
        }

        return array('facets' => $facets, 'panels' => $panels, 'fields' => $fields);
    }

    /**
     * @EXT\Route(
     *     "pia/user/{user}/presences/widget",
     *     name="formalibre_pia_presences_widget",
     *     defaults={"periodeId"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("FormaLibrePiaBundle::piaPresencesWidget.html.twig")
     */
    public function piaPresencesWidgetAction(User $user)
    {
        $this->checkOpen();
        $datas = array();
        $sessions = array();
        $userSessions = $this->cursusManager->getSessionsByUserAndType($user);

        foreach ($userSessions as $session) {

            if ($session->getSessionStatus() !== CourseSession::SESSION_CLOSED) {
                $sessions[] = $session;
            }
        }
        $allPresences = $this->presenceManager
            ->getPresencesByUserAndSessions($user, $sessions);
        $presences = $this->presenceManager
            ->getPresencesByUserAndSessionAndStatusName($user, $sessions, 'Présent');

        $totalPresences = array();
        $actualPresences = array();

        foreach ($allPresences as $presence) {
            $sessionId = $presence->getCourseSession()->getId();

            if (!isset($totalPresences[$sessionId])) {
                $totalPresences[$sessionId] = 0;
            }
            $totalPresences[$sessionId]++;
        }

        foreach ($presences as $presence) {
            $sessionId = $presence->getCourseSession()->getId();

            if (!isset($actualPresences[$sessionId])) {
                $actualPresences[$sessionId] = 0;
            }
            $actualPresences[$sessionId]++;
        }

        foreach($sessions as $session) {
            $sessionId = $session->getId();
            $percentage = 0;

            if (isset($totalPresences[$sessionId]) &&
                isset($actualPresences[$sessionId]) &&
                $totalPresences[$sessionId] > 0 &&
                $actualPresences[$sessionId] > 0
            ) {
                $percentage = floor(($actualPresences[$sessionId] / $totalPresences[$sessionId]) * 100);
            }
            $datas[] = array('session' => $session, 'percentage' => $percentage);
        }

        return array('datas' => $datas);
    }

    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

    private function checkOpenPrintPdf(Request $request = NULL)
    {
        //$ServerIp =  system("curl -s ipv4.icanhazip.com");

        if ($this->authorization->isGranted('ROLE_BULLETIN_ADMIN') or $this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }
        elseif (!is_null($request) && $request->getClientIp() === '127.0.0.1'){
            return true;
        }

        elseif (!is_null($request) && $request->getClientIp() == '91.121.211.13'){
            return true;
        }

        throw new AccessDeniedException();
    }

    public function calculIndice(User $user)
    {
        $allPoints = $this->bulletinManager->getAllUserPointsDatas($user);

        return $allPoints['finalPercentage'];
    }
}
