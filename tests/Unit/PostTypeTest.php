<?php
namespace PykamQA\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PykamQA\PostType;

class PostTypeTest extends TestCase
{
    public function test_posttype_class_exists()
    {
        $this->assertTrue(class_exists(PostType::class));
    }

    public function test_posttype_has_register_method()
    {
        $pt = new PostType();
        $this->assertTrue(method_exists($pt, 'register_post_type'));
    }

    public function test_posttype_constant_is_defined()
    {
        $this->assertTrue(defined('PykamQA\PostType::POST_NAME'));
        $this->assertEquals('pykam-qa', PostType::POST_NAME);
    }
}
