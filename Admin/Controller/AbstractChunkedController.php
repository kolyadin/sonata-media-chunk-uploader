<?php

namespace ADW\SonataMediaChunkUploader\Admin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ADW\SonataMediaChunkUploader\Service\ChunkServiceInterface;

/**
 * Class AbstractChunkedController
 * @package ADW\SonataMediaChunkUploader\Admin\Controller
 */
abstract class AbstractChunkedController extends AbstractController
{
    abstract public function upload(Request $request);

    /**
     *  This function must return an array containing the following
     *  keys and their corresponding values:
     *    - last: Wheter this is the last chunk of the uploaded file
     *    - uuid: A unique id which distinguishes two uploaded files
     *            This uuid must stay the same among the task of
     *            uploading a chunked file.
     *    - index: A numerical representation of the currently uploaded
     *            chunk. Must be higher that in the previous request.
     *    - orig: The original file name.
     *
     * @param Request $request - The request object
     *
     * @return array
     */
    abstract protected function parseChunkedRequest(Request $request);

    /**
     *  This function will be called in order to upload and save an
     *  uploaded chunk.
     *
     *  This function also calls the chunk manager if the function
     *  parseChunkedRequest has set true for the "last" key of the
     *  returned array to reassemble the uploaded chunks.
     *
     * @param UploadedFile          $file         - The uploaded chunk
     * @param Request               $request      - The request object
     * @param ChunkServiceInterface $chunkManager - Chunk service
     *
     * @return mixed
     */
    protected function handleChunkedUpload(UploadedFile $file, Request $request)
    {
        $chunkManager = $this->container->get('adw.sonata.chunks.service');

        // get information about this chunked request
        [ $last, $uuid, $index, $orig ] = $this->parseChunkedRequest($request);

        $chunkManager->addChunk($uuid, $index, $file, $orig);

        if ($chunkManager->getLoadDistribution()) {
            $chunks    = $chunkManager->getChunks($uuid);
            $assembled = $chunkManager->assembleChunks($chunks, true, $last);
        }

        // if all chunks collected and stored, proceed
        // with reassembling the parts
        if ($last) {
            if (!$chunkManager->getLoadDistribution()) {
                $chunks    = $chunkManager->getChunks($uuid);
                $assembled = $chunkManager->assembleChunks($chunks, true, true);
            }

            return $assembled->getRealPath();
        }
    }

    /**
     *  Flattens a given filebag to extract all files.
     *
     * @param FileBag $bag The filebag to use
     *
     * @return array An array of files
     */
    protected function getFiles(FileBag $bag) : array
    {
        $files        = [];
        $fileBag      = $bag->all();
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($fileBag), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($fileIterator as $file) {
            if (is_array($file) || null === $file) {
                continue;
            }

            $files[] = $file;
        }

        return $files;
    }
}
