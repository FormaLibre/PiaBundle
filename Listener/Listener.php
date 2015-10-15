<?php

namespace FormaLibre\PiaBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service()
 */
class Listener
{
    private $container;

    /**
     * @param ContainerInterface $container
     * @DI\InjectParams({
     *      "container" = @DI\Inject("service_container"),
     *      "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack, HttpKernelInterface $httpKernel)
    {
        $this->container = $container;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("open_tool_desktop_formalibre_pia_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate(array(), null, array("_controller" => 'FormaLibrePiaBundle:Pia:index'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());

    }

    /**
     * @DI\Observe("administration_tool_formalibre_pia_admin_tool")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenAdminTool(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibrePiaBundle:PiaAction:ActionList';
        $this->redirect($params, $event);
    }


    private function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

}
