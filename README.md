# Sonata media chunk uploader #

Bundle allows you to upload large files from admin-panel by chunks, use custom form-type `LargeMediaType` and 
sonata media provider (fixed issue with memory) `sonata.media.provider.large_file`.

## Installation

```
composer require kolyadin/sonata-media-chunk-uploader:*
```

Add routes:
```
chunk_uploader:
    resource: "@SonataMediaChunkUploaderBundle/Admin/Controller/"
    type:     annotation
```

Add custom widget to twig config:
```
twig:
    form_themes:
        - '@SonataMediaChunkUploader/Form/fields.html.twig'
```

## Configuration (optional)
```
sonata_media_chunk_uploader:
  chunks:
    chunk_folder: "%kernel.root_dir%/../web/uploads/media/chunks" 
    chunk_size: 3000 # in bytes
    load_distribution: true
    maxage: 604800
  storage:
    type: 'filesystem'
```