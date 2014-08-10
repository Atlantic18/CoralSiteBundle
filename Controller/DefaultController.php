<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/menu/root/{max_level}/{active_node}")
     */
    public function rootAction($max_level, $active_node = null)
    {
        $items = $this->get('coral_connect')->doGetRequest('/v1/node/list')->getMandatoryParam('items[0].items');

        return $this->render(
            'CoralSiteBundle:Menu:root.html.twig',
            array('items' => $items, 'max_level' => $max_level - 1, 'active_node' => $active_node)
        );
    }
}
