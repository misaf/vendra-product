<?php

declare(strict_types=1);

namespace Misaf\VendraProduct\Tests\Feature;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'tags')]
final class ProductTestTag extends Model
{
    use HasFactory;
}
