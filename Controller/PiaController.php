<?php
namespace Laurent\PiaBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
class BulletinController extends Controller
{
    /**
     * @EXT\Route("/", name="laurentPiaIndex")
     */
    public function indexAction()
    {
        return $this->render('LaurentPiaBundle::PiaIndex.html.twig');
    }
}
