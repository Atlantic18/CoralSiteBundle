<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ContentController extends Controller
{
    private function createInitialContentByRenderer($renderer)
    {
        if($renderer == "html")
        {
            return '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ultricies dolor diam. Aliquam vitae lobortis nisi. Nulla turpis ligula, iaculis in sem ut, consequat sollicitudin felis. Etiam accumsan auctor mi, vel pretium ipsum ullamcorper nec. Vivamus eget justo non neque porta tristique sed at justo. Mauris nibh urna, rutrum ac massa eu, varius ornare quam. Proin eget vestibulum ipsum, id tristique magna. Integer eu justo velit. Proin tincidunt auctor metus in vehicula. Nulla elementum luctus consectetur.</p>';
        }
        if($renderer == 'json')
        {
            return '
{
    "key": "value"
}
            ';
        }

        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ultricies dolor diam. Aliquam vitae lobortis nisi. Nulla turpis ligula, iaculis in sem ut, consequat sollicitudin felis. Etiam accumsan auctor mi, vel pretium ipsum ullamcorper nec. Vivamus eget justo non neque porta tristique sed at justo. Mauris nibh urna, rutrum ac massa eu, varius ornare quam. Proin eget vestibulum ipsum, id tristique magna. Integer eu justo velit. Proin tincidunt auctor metus in vehicula. Nulla elementum luctus consectetur.';
    }

    /**
     * @Route("/content-add/{slug}/{id}/{section}/{renderer}", name="content_add_section")
     * @Method("GET")
     */
    public function addSectionAction($slug, $id, $section, $renderer)
    {
        $this->get('coral_connect')->doPostRequest("/v1/content/add/$id/$section", array(
            'content'  => $this->createInitialContentByRenderer($renderer),
            'renderer' => $renderer
        ));

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/content-add/{slug}/{id}/after/{permid}/{renderer}", name="content_add_after")
     * @Method("GET")
     */
    public function addAfterAction($slug, $id, $permid, $renderer)
    {
        $this->get('coral_connect')->doPostRequest("/v1/content/add/$id/after/$permid", array(
            'content'  => $this->createInitialContentByRenderer($renderer),
            'renderer' => $renderer
        ));

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/content-update/{slug}/{permid}", name="content_update")
     * @Method("POST")
     */
    public function updateAction($slug, $permid)
    {
        $request = $this->getRequest();
        $this->get('coral_connect')->doPostRequest('/v1/content/update/' . $permid, array(
            'content'  => $request->request->get('content'),
            'renderer' => $request->request->get('renderer')
        ));

        if($request->isXmlHttpRequest())
        {
            return new Response('OK');
        }

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/content-move-up/{slug}/{permid}", name="content_move_up")
     * @Method("GET")
     */
    public function moveUpAction($slug, $permid)
    {
        $this->get('coral_connect')->doPostRequest('/v1/content/move-up/' . $permid);

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/content-down-up/{slug}/{permid}", name="content_move_down")
     * @Method("GET")
     */
    public function moveDownAction($slug, $permid)
    {
        $this->get('coral_connect')->doPostRequest('/v1/content/move-down/' . $permid);

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/content-delete/{slug}/{permid}", name="content_delete")
     * @Method("GET")
     */
    public function deleteAction($slug, $permid)
    {
        $this->get('coral_connect')->doDeleteRequest('/v1/content/delete/' . $permid);

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }
}
