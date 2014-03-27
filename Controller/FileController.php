<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Coral\FileBundle\Entity\File;
use Coral\FileBundle\Entity\FileAttribute;

class FileController extends Controller
{
    private function generateFilename($slug, $mimeType)
    {
        $maxId = $this->getDoctrine()->getManager()->createQuery(
                'SELECT MAX(f.id) FROM CoralFileBundle:File f'
            )
            ->getSingleScalarResult();

        return $slug . '-' . ($maxId + 1) . '.' . substr($mimeType, 6);
    }

    private function getAccount()
    {
        return $this->getDoctrine()->getManager()->createQuery(
                'SELECT a FROM CoralCoreBundle:Account a WHERE a.id = 1'
            )
            ->getSingleResult();
    }

    /**
     * @Route("/admin/file-upload/{slug}", name="file_upload")
     * @Method("POST")
     */
    public function addSectionAction($slug)
    {
        try {
            $hash = sha1($this->get("request")->getContent());

            $file = $this->getDoctrine()
                ->getRepository('CoralFileBundle:File')
                ->findOneByHash($hash);

            if($file && $file instanceof File)
            {
                return new JsonResponse(array(
                    'status'  => 'ok',
                    'link'    => $this->generateUrl('file_link', array('id' => $file->getId()))
                ), 201);
            }

            $filename =  $this->container->getParameter("kernel.cache_dir") . "/coral-upload-$hash.jpg";
            file_put_contents($filename, $this->get("request")->getContent());

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filename);
            finfo_close($finfo);

            if(!strpos($mimeType, 'image/') === false)
            {
                unlink($filename);
                throw new \Exception("Invalid mime type: " . $mimeType);
            }

            $em = $this->getDoctrine()->getManager();

            $newFilename = $this->generateFilename($slug, $mimeType);
            $newFilePath = $this->container->getParameter("kernel.root_dir") . "/../web/uploads/original/$newFilename";

            $file = new File;
            $file->setFilename($newFilename);
            $file->setMimeType($mimeType);
            $file->setHash($hash);
            $file->setAccount($this->getAccount());

            $fileAttribute = new FileAttribute;
            $fileAttribute->setName('node-slug');
            $fileAttribute->setValue($slug);
            $fileAttribute->setFile($file);

            copy($filename, $newFilePath);

            $em->persist($file);
            $em->persist($fileAttribute);
            $em->flush();

            return new JsonResponse(array(
                'status'  => 'ok',
                'link'    => $this->generateUrl('file_link', array('id' => $file->getId()))
            ), 201);
        }
        catch (\Exception $e)
        {
            @unlink($newFilePath);
            return new JsonResponse(array(
                'status'  => 'failed',
                'message' => $e->getMessage()
            ), 500);
        }
    }

    /**
     * @Route("/admin/file-link/{id}", name="file_link")
     * @Method("GET")
     */
    public function linkAction($id)
    {
        $file = $this->getDoctrine()
            ->getRepository('CoralFileBundle:File')
            ->find($id);

        return $this->render(
            'CoralSiteBundle:File:link.html.twig',
            array('file' => $file)
        );
    }
}
