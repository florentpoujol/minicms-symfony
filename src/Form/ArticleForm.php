<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ArticleForm extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,  [
                'attr' => [
                    'maxlength' => 200, // the doc this should be automatic, introspected from the entity's property constraints, but it isn't ?
                ],
            ])
            ->add('content', TextareaType::class, [
                'attr' => [
                    'maxlength' => 99_999,
                ],
            ])
            ->add('allow_comments', CheckboxType::class, [
                'required' => false,
            ])
            ->add('published_at', DateTimeType::class, [
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save article',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
