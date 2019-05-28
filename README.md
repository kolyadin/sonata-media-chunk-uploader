# Sonata media chunk uploader #

Bundle allows you to upload large files from admin-panel by chunks, use custom form-type `LargeMediaType` and 
sonata media provider (fixed issue with memory) `sonata.media.provider.large_file`.

## Installation

Add repository to composer.json:
```
"repositories": [
    {"type": "vcs", "url": "https://gitlab.cloud.isobar.ru/reusable/sonata-media-chunk-uploader.git"}        
]
```

Use composer to require package:
```
# bash
composer require adw/sonata-media-chunk-uploader dev-master
```

Add routes:
```
adw_chunk_uploader:
    resource: "@ADWSonataMediaChunkUploaderBundle/Admin/Controller/"
    type:     annotation
```

Add custom widget to twig config:
```
twig:
    form_themes:
        - '@ADWSonataMediaChunkUploader/Form/fields.html.twig'
```

## Configuration (optional)
```
adw_sonata_media_chunk_uploader:
  chunks:
    chunk_folder: "%kernel.root_dir%/../web/uploads/media/chunks" 
    chunk_size: 3000 # in bytes
    load_distribution: true
  storage:
    type: 'filesystem'
```