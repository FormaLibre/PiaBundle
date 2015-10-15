<?php

namespace FormaLibre\PiaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.manager.pia_manager")
 */
class PiaManager
{
    private $facetManager;
    private $om;

    private $fieldFacetRepo;
    private $panelFacetRepo;

    /**
     * @DI\InjectParams({
     *      "facetManager" = @DI\Inject("claroline.manager.facet_manager"),
     *      "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     */

    public function __construct(
        FacetManager $facetManager,
        ObjectManager $om
    )
    {
        $this->facetManager = $facetManager;
        $this->om = $om;

        $this->fieldFacetRepo = $om->getRepository('ClarolineCoreBundle:Facet\FieldFacet');
        $this->panelFacetRepo = $om->getRepository('ClarolineCoreBundle:Facet\PanelFacet');
    }

    public function getAllFacets()
    {
        return $this->facetManager->getFacets();
    }

    public function getAllPanelFacets()
    {
        return $this->panelFacetRepo->findBy(
            array(),
            array('position' => 'ASC')
        );
    }

    public function getAllFieldFacets()
    {
        return $this->fieldFacetRepo->findBy(
            array(),
            array('position' => 'ASC')
        );
    }

    public function getFieldFacetValuesByUser(User $user)
    {
        return $this->facetManager->getFieldValuesByUser($user);
    }
}
