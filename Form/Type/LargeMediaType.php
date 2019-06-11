<?php

namespace ADW\SonataMediaChunkUploader\Form\Type;

use App\Application\Sonata\MediaBundle\Entity\Media;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Sonata\MediaBundle\Form\Type\MediaType;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class LargeMediaType
 * @package ADW\SonataMediaChunkUploader\Form\Type
 */
class LargeMediaType extends MediaType
{
    protected $class;

    /**
     * LargeMediaType constructor.
     *
     * @param Pool $pool
     * @param      $class
     */
    public function __construct(Pool $pool, $class)
    {
        $this->class = $class;

        parent::__construct($pool, $class);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataTransformer = new ProviderDataTransformer($this->pool, $this->class, [
            'provider'      => $options['provider'],
            'context'       => $options['context'],
            'empty_on_new'  => $options['empty_on_new'],
            'new_on_update' => $options['new_on_update'],
        ]);

        $dataTransformer->setLogger($this->logger);

        $builder->addModelTransformer($dataTransformer);
        $builder->add('file', HiddenType::class, ['mapped' => false]);
        $builder->add('unlink', CheckboxType::class, [
            'label' => 'widget_label_unlink',
            'mapped' => false,
            'data' => false,
            'required' => false,
        ]);

        $this->pool->getProvider($options['provider'])->buildMediaType($builder);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if($data['file'] !== null and file_exists($data['file'])) {
                $file = new UploadedFile($data['file'], basename($data['file']), null, null, null, true);
                $data['binaryContent'] = $file;
                $event->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($options) {
            if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
                $event->setData(null);
                /** @var Media $media */
                $media = $event->getForm()->getNormData();

                if($media instanceof Media and $media->getId()) {
                    try {
                        $pathToDelete = $this->pool->getProvider($options['provider'])->getReferenceFile($media);
                        $pathToDelete->delete();
                    } catch (\Exception $exception) {
                        // log
                    }
                }
            }
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => $this->class,
                'empty_on_new' => true,
                'new_on_update' => true,
                'translation_domain' => 'SonataMediaBundle',
            ])
            ->setRequired(['provider', 'context'])
            ->setAllowedTypes('provider', 'string')
            ->setAllowedTypes('context', 'string')
            ->setAllowedValues('provider', $this->pool->getProviderList())
            ->setAllowedValues('context', array_keys($this->pool->getContexts()));
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