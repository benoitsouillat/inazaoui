<?php

declare(strict_types=1);

namespace App\Tests\Integration\Form;

use App\Form\ChangePasswordFormType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChangePasswordFormTest extends KernelTestCase
{
    public function testPasswordIsTooShort(): void
    {
        self::bootKernel();

        $formFactory = static::getContainer()->get('form.factory');

        $form = $formFactory->create(ChangePasswordFormType::class);

        $form->submit([
            'plainPassword' => [
                'first' => '123',
                'second' => '123'
            ]
        ]);
        $this->assertFalse($form->isValid());
        $errors = $form->get('plainPassword')->get('first')->getErrors();

        $this->assertCount(1, $errors);
        $this->assertSame('Your password should be at least 8 characters', $errors[0]->getMessage());
    }
}
