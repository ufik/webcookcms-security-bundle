<?php

namespace Webcook\Cms\SecurityBundle\Tests\Form\Type;

use Webcook\Cms\SecurityBundle\Form\Type\ResourcesType;
use Symfony\Component\Form\Test\TypeTestCase;

class ResourcesTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $formData = array(
            'test' => 'test',
        );

        $form = $this->factory->create(ResourcesType::class);

        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
    }
}
