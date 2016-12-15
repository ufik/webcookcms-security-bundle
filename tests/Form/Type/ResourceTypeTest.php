<?php

namespace Webcook\Cms\SecurityBundle\Tests\Form\Type;

use Webcook\Cms\SecurityBundle\Form\Type\ResourceType;
use Symfony\Component\Form\Test\TypeTestCase;

class ResourceTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'view' => true,
            'edit' => false,
            'delete' => false,
        );

        $form = $this->factory->create(ResourceType::class);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
    }
}
