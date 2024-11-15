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
use function Symfony\Component\Translation\t;

/**
 * @extends AbstractType<Article>
 */
final class ArticleForm extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,  [
                'label' => t('article_form.title'),
                'attr' => [
                    'maxlength' => 200, // the doc this should be automatic, introspected from the entity's property constraints, but it isn't ?
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => t('article_form.content'),
                'attr' => [
                    'maxlength' => 99_999,
                ],
            ])
            ->add('allow_comments', CheckboxType::class, [
                'label' => t('article_form.allow_comments'),
                'required' => false,
            ])
            ->add('published_at', DateTimeType::class, [
                'label' => t('article_form.published_at'),
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => t('article_form.save'),
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
