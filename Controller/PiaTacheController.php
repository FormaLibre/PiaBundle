<?php

namespace FormaLibre\PiaBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use FormaLibre\BulletinBundle\Manager\BulletinManager;
use FormaLibre\PiaBundle\Entity\Taches;
use FormaLibre\PiaBundle\Form\TacheType;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Claroline\MessageBundle\Manager\MessageManager;

class PiaTacheController extends Controller
{
    private $authorization;
    private $bulletinManager;
    private $em;
    private $om;
    private $messageManager;

    /** @var actionsRepository */
    private $actionsRepo;
    /** @var suivisRepository */
    private $suivisRepo;
    /** @var tachesRepository */
    private $tachesRepo;

    /**
     * @DI\InjectParams({
     *      "authorization"   = @DI\Inject("security.authorization_checker"),
     *      "bulletinManager" = @DI\Inject("formalibre.manager.bulletin_manager"),
     *      "em"              = @DI\Inject("doctrine.orm.entity_manager"),
     *      "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *      "messageManager"     = @DI\Inject("claroline.manager.message_manager")
     * })
     */

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BulletinManager $bulletinManager,
        EntityManager $em,
        ObjectManager $om,
        MessageManager $messageManager
    )
    {
        $this->authorization = $authorization;
        $this->bulletinManager = $bulletinManager;
        $this->em = $em;
        $this->om = $om;
        $this->messageManager = $messageManager;

        $this->actionsRepo = $om->getRepository('FormaLibrePiaBundle:Actions');
        $this->suivisRepo = $om->getRepository('FormaLibrePiaBundle:Suivis');
        $this->tachesRepo = $om->getRepository('FormaLibrePiaBundle:Taches');
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
     * @EXT\Route("/tache/add/{user}", name="formalibrePiaTacheAdd", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibrePiaBundle::TacheForm.html.twig")
     */
    public function PiaTacheAddAction(Request $request, User $user)
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
            
            $object="Concerne:".$taches->getEleves();
            $content="Concerne: ".$taches->getEleves()."<br>"."Action :".$taches->getAction()."<br><br>".$taches->getTitre()."<br><br>".$taches->getCommentaire();
            $users=[$taches->getResponsable()];
            $message=$this->messageManager->create($content, $object,$users);
            $this->messageManager->send($message);
            
            }
            return new Response('success', 202);
        }
        return array('form' => $form->createView(), 'action' => $this->generateUrl('formalibrePiaTacheAdd', array('user'=>$user->getId())));
    }

    /**
     * @EXT\Route("/tache/{tache}/edit", name="formalibrePiaTacheEdit", options = {"expose"=true})
     *
     * @EXT\Template("FormaLibrePiaBundle::TacheForm.html.twig")
     */
    public function PiaTacheEditAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $form = $this->createForm(new TacheType, $tache);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->em->persist($tache);
                $this->em->flush();
            }
            return new Response('success', 202);
        }

        return array('form' => $form->createView(), 'action' => $this->generateUrl('formalibrePiaTacheEdit', array('tache'=>$tache->getId())));
    }

    /**
     * @EXT\Route("/tache/{tache}/delete", name="formalibrePiaTacheDelete", options = {"expose"=true})
     *
     */
    public function PiaTacheDeleteAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $this->em->remove($tache);
        $this->em->flush();

        return new Response('success', 200);
    }

    /**
     * @EXT\Route("/tache/{tache}/close", name="formalibrePiaTacheClose", options = {"expose"=true})
     *
     */
    public function PiaTacheCloseAction(Request $request, Taches $tache)
    {
        $this->checkOpen();
        $tache->setFini(1);
        $this->em->persist($tache);
        $this->em->flush();

        return new Response('success', 200);
    }



    private function checkOpen()
    {
        if ($this->authorization->isGranted('ROLE_PROF')) {
            return true;
        }

        throw new AccessDeniedException();
    }

}
