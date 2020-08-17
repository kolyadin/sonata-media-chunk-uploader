<?php

namespace Kolyadin\SonataMediaChunkUploader\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface ChunkStorageInterface
 * @package Kolyadin\SonataMediaChunkUploader\Storage
 */
interface ChunkStorageInterface
{
    /**
     * @param $maxAge
     *
     * @return mixed
     */
    public function clear($maxAge);

    /**
     * @param              $uuid
     * @param              $index
     * @param UploadedFile $chunk
     * @param              $original
     *
     * @return mixed
     */
    public function addChunk($uuid, $index, UploadedFile $chunk, $original);

    /**
     * @param $chunks
     * @param $removeChunk
     * @param $renameChunk
     *
     * @return mixed
     */
    public function assembleChunks($chunks, $removeChunk, $renameChunk);

    /**
     * @param $path
     *
     * @return mixed
     */
    public function cleanup($path);

    /**
     * @param $uuid
     *
     * @return mixed
     */
    public function getChunks($uuid);
}
