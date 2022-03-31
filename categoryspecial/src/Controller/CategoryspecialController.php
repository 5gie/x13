<?php

declare(strict_types=1);

namespace PrestaShop\Module\Categoryspecial;

use Category;
use PrestaShop\PrestaShop\Core\Domain\Category\Exception\CategoryException;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class CategoryspecialController extends FrameworkBundleAdminController
{

    /**
     * Toggle category status.
     *
     * @param int $categoryId
     *
     * @return JsonResponse
     */
    public function toggleSpecialAction($categoryId)
    {
        if ($this->isDemoModeEnabled()) {
            return $this->json([
                'status' => false,
                'message' => $this->getDemoModeErrorMessage(),
            ]);
        }

        try {
            
            $category = new Category((int) $categoryId);

            $category->is_special = !$category->is_special;

            $category->update();

            $response = [
                'status' => true,
                'message' => $this->trans('The special has been successfully updated.', 'Admin.Notifications.Success'),
            ];
        } catch (CategoryException $e) {
            $response = [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        return $this->json($response);
    }
}