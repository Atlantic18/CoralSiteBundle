<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class NodeController extends Controller
{
    private function createNodeFormBuilder($nodeArray)
    {
        $form = $this->createFormBuilder();

        $form->setAction($this->generateUrl('update_node', array('id' => $nodeArray['id'])));
        $form->setMethod('POST');

        $form->add('name', 'text', array(
                        'data' => $nodeArray['name'],
                        'constraints' => array(
                            new NotBlank(),
                            new Length(array('min' => 3)),
                        ),
                    ));
        $form->add('slug', 'text', array(
                        'data' => $nodeArray['slug'],
                        'constraints' => array(
                            new NotBlank(),
                            new Length(array('min' => 3)),
                        ),
                    ));

        unset($nodeArray['id'], $nodeArray['token'], $nodeArray['name'], $nodeArray['slug'], $nodeArray['status']);

        foreach ($nodeArray as $key => $value) {
            if(!is_array($value)) {
                if($key != 'id' && $key != 'token') {
                    $form->add($key, 'text', array(
                        'data' => $value
                    ));
                }
            }
        }
        $form
            ->add('new_param_name', 'text')
            ->add('new_param_value', 'text');

        return $form;
    }

    private function getSingleNodeFromList($uri)
    {
        $items = $this->get('coral_connect')->doGetRequest($uri)->getMandatoryParam('items');

        if(is_array($items) && count($items) == 1) {
            return $items[0];
        }

        throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException("Invalid number of items, one expected");
    }

    /**
     * @Route("/list-nodes", name="list_nodes")
     * @Method("GET")
     */
    public function listNodesAction()
    {
        $root = $this->getSingleNodeFromList('/v1/node/list');

        return $this->render(
            'CoralSiteBundle:Node:sitemap.html.twig',
            array(
                'root' => $root,
                'node' => $root,
                'form' => $this->createNodeFormBuilder($root)->getForm()->createView()
            )
        );
    }

    /**
     * @Route("/list-node/{id}", name="list_node", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function listNodeAction($id)
    {
        $root = $this->getSingleNodeFromList('/v1/node/list');
        $node = $this->get('coral_connect')->doGetRequest('/v1/node/info/' . $id)->getParams();

        return $this->render(
            'CoralSiteBundle:Node:sitemap.html.twig',
            array(
                'root' => $root,
                'node' => $node,
                'form' => $this->createNodeFormBuilder($node)->getForm()->createView()
            )
        );
    }

    /**
     * @Route("/update-node/{id}", name="update_node", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function updateNodeAction(Request $request)
    {
        $node = $this->get('coral_connect')->doGetRequest('/v1/node/info/' . $request->get('id'))->getParams();
        $form = $this->createNodeFormBuilder($node)->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            if($data['new_param_name'] && $data['new_param_value'])
            {
                $data[$data['new_param_name']] = $data['new_param_value'];
            }
            unset($data['new_param_name'], $data['new_param_value']);

            $this->get('coral_connect')->doPostRequest(
                '/v1/node/update/' . $request->get('id'),
                $data
            );

            $node = $this->get('coral_connect')->doGetRequest('/v1/node/info/' . $request->get('id'))->getParams();
            $form = $this->createNodeFormBuilder($node)->getForm();
        }

        return $this->render(
            'CoralSiteBundle:Node:sitemap.html.twig',
            array(
                'root' => $this->getSingleNodeFromList('/v1/node/list'),
                'node' => $node,
                'form' => $form->createView()
            )
        );
    }

    /**
     * @Route("/move-node/{id_what}/{position}/{id_where}", name="move_node", requirements={"id_what" = "\d+", "position" = "last-child-of|after|before", "id_where" = "\d+"})
     * @Method("POST")
     */
    public function moveNodeAction($id_what, $position, $id_where)
    {
        $this->get('coral_connect')->doPostRequest("/v1/node/move/$id_what/$position/$id_where");

        $root = $this->getSingleNodeFromList('/v1/node/list');
        $node = $this->get('coral_connect')->doGetRequest('/v1/node/info/' . $id_what)->getParams();

        return $this->render(
            'CoralSiteBundle:Node:sitemap.html.twig',
            array(
                'root' => $root,
                'node' => $node,
                'form' => $this->createNodeFormBuilder($node)->getForm()->createView()
            )
        );
    }

    /**
     * @Route("/node-edit/{slug}", name="edit_node")
     * @Method("GET")
     */
    public function editAction($slug)
    {
        $node = $this->get('coral_connect')->doGetRequest('/v1/node/detail/latest/' . $slug)->getParams();

        return $this->render(
            'CoralSiteBundle:Node:edit.html.twig',
            array('node' => $node)
        );
    }

    /**
     * @Route("/node-publish/{slug}/{id}", name="publish_node", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function publishAction($slug, $id)
    {
        $this->get('coral_connect')->doPostRequest('/v1/content/publish/' . $id);

        return $this->redirect($this->generateUrl('edit_node', array('slug' => $slug)));
    }

    /**
     * @Route("/node-delete/{id}", name="delete_node", requirements={"id" = "\d+"})
     * @Method("GET")
     */
    public function deleteAction($id)
    {
        $request = $this->getRequest();
        $node = $this->get('coral_connect')->doDeleteRequest('/v1/node/delete/' . $id);

        $this->get('session')->getFlashBag()->add('notice', 'node.deleted');

        if($request->isXmlHttpRequest())
        {
            $root = $this->getSingleNodeFromList('/v1/node/list');

            return $this->render(
                'CoralSiteBundle:Node:sitemap.html.twig',
                array(
                    'root' => $root,
                    'node' => $root,
                    'form' => $this->createNodeFormBuilder($root)->getForm()->createView()
                )
            );
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/node-add/{id}", name="add_node", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function addAction($id)
    {
        $response = $this->get('coral_connect')
            ->doPostRequest('/v1/node/add/after/' . $id, array(
                'name' => 'New node',
                'slug' => 'slug' . substr(sha1(rand()), 0, 16)
            ));
        $node = $this->get('coral_connect')
            ->doGetRequest('/v1/node/info/' . $response->getMandatoryParam('id'))
            ->getParams();

        return $this->render(
            'CoralSiteBundle:Node:sitemap.html.twig',
            array(
                'root' => $this->getSingleNodeFromList('/v1/node/list'),
                'node' => $node,
                'form' => $this->createNodeFormBuilder($node)->getForm()->createView()
            )
        );
    }
}
