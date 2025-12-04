<?php
namespace PykamQA\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PykamQA\MetaBox;

class MetaBoxTest extends TestCase
{
    public function test_metabox_class_exists()
    {
        $this->assertTrue(class_exists(MetaBox::class));
    }

    public function test_metabox_has_register_method()
    {
        $meta = new MetaBox();
        $this->assertTrue(method_exists($meta, 'register'));
    }

    public function test_metabox_constants_are_defined()
    {
        $this->assertTrue(defined('PykamQA\MetaBox::ANSWER'));
        $this->assertTrue(defined('PykamQA\MetaBox::QUESTION_AUTHOR'));
        $this->assertTrue(defined('PykamQA\MetaBox::ANSWER_AUTHOR'));
        $this->assertTrue(defined('PykamQA\MetaBox::ANSWER_DATE'));
        $this->assertTrue(defined('PykamQA\MetaBox::ATTACHED_POST'));
    }
}
