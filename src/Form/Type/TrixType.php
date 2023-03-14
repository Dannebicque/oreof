<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TrixType extends AbstractType
{
    private string $id;

    private bool $enabled = true;

    /**
     * @param bool $flag
     */
    public function setEnabled(bool $flag): void
    {
        $this->enabled = $flag;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['trixId'] = substr(sha1(uniqid($view->vars['id'], true)), -6);
        $view->vars['enabled'] = $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextareaType::class;
    }
}
