<?php
namespace PykamQA\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PykamQA\PykamQA;

class PykamQATest extends TestCase
{
    public function test_pykamqa_class_exists()
    {
        $this->assertTrue(class_exists(PykamQA::class));
    }

    public function test_pykamqa_constructor_accepts_parameters()
    {
        $instance = new PykamQA(5, 10);
        $this->assertInstanceOf(PykamQA::class, $instance);
    }

    public function test_pykamqa_has_render_method()
    {
        $instance = new PykamQA(5, 1);
        $this->assertTrue(method_exists($instance, 'print'));
    }
}
