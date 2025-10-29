<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductType;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Post(
 *     path="/admin/generate-menu",
 *     summary="Generate menu and store in settings",
 *     tags={"Admin"},
 *     security={{"bearer_token":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Menu generated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="menu", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 */
class MenuController extends Controller
{
    public function generateMenu(Request $request)
    {
        // Privilege check removed; handled by middleware

        $productTypes = ProductType::where('status', 'Active')
            ->whereHas('categories', function ($query) {
                $query->where('status', 'Active');
            })
            ->orderBy('sort_order')
            ->get();

        $menu = $productTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
                'sort_order' => $type->sort_order,
            ];
        })->toArray();

        Setting::updateOrCreate(
            ['key' => 'menu'],
            ['value' => $menu]
        );

        return response()->json(['menu' => $menu], 200);
    }
} 