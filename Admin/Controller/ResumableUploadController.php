<?php

namespace Kolyadin\SonataMediaChunkUploader\Admin\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Kolyadin\SonataMediaChunkUploader\Service\ChunkService;

/**
 * Class ResumableUploadController
 * @package Kolyadin\SonataMediaChunkUploader\Admin\Controller
 * @Route("/admin/chunks")
 */
class ResumableUploadController extends AbstractChunkedController
{

    protected $chunkManager;

    public function __construct(ChunkService $chunkManager)
    {
        $this->chunkManager = $chunkManager;
    }

    /**
     * @Route("/upload", name="chunks.upload", methods={"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function upload(Request $request)
    {
        $files = $this->getFiles($request->files);
        $path = null;

        foreach($files as $file) {
            try {
                $path = $this->handleChunkedUpload($file, $request, $this->chunkManager);
            } catch (\Exception $exception) {
                return new Response($exception->getMessage(), 403);
            }
        }

        return new JsonResponse(['message' => 'success', 'file' => $path ? $path : 'processing.'], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function parseChunkedRequest(Request $request)
    {
        $session = $this->container->get('session');

        $orig  = $request->get('resumableFilename');
        $index = $request->get('resumableChunkNumber');
        $last  = (int) $request->get('resumableTotalChunks') === (int) $request->get('resumableChunkNumber');

        // it is possible, that two clients send a file with the
        // exact same filename, therefore we have to add the session
        // to the uuid otherwise we will get a mess
        $uuid = md5(sprintf('%s.%s', $orig, $session->getId()));

        return [$last, $uuid, $index, $orig];
    }
}
