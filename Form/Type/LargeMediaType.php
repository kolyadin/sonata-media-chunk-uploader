<?php

namespace ADW\SonataMediaChunkUploader\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Sonata\MediaBundle\Form\Type\MediaType;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class LargeMediaType
 * @package ADW\SonataMediaChunkUploader\Form\Type
 */
class LargeMediaType extends MediaType
{
    protected $class;

    public function __construct(Pool $pool, $class)
    {
        $this->class = $class;

        parent::__construct($pool, $class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('context', HiddenType::class, ['data' => $options['context'] ?? 'default'])
            ->add('providerName', HiddenType::class, ['data' => $options['provider']])
            ->add('file', HiddenType::class, ['mapped' => false])
            ->add('binaryContent', FileType::class);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if($data['file'] !== null and file_exists($data['file'])) {
                $file = new UploadedFile($data['file'], basename($data['file']), null, null, null, true);
                $data['binaryContent'] = $file;
                $event->setData($data);
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => $this->class,
           'provider' => '',
           'context' => 'default',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adw_large_media';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}