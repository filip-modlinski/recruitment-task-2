<?php


namespace App\Controller;


use App\WroclawGo\ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CulturalOffersController
 * @package App\Controller
 */
class CulturalOffersController extends AbstractController
{
    /**
     * @Route("/oferty-kulturalne/{page<\d+>?1}", methods="GET", name="show_cultural_offers")
     * @param Request $request
     * @param ApiClient $apiClient
     * @param int $page
     * @return Response
     */
    public function showAction(Request $request, ApiClient $apiClient, int $page): Response
    {
        $culturalOffersCategories = $apiClient->getCulturalOffersCategories();

        $selectedCategoryAlias = !is_null($request->query->get('kategoria')) ? $request->query->get('kategoria') : 'wszystkie';
        $selectedCategoryId = null;
        foreach ($culturalOffersCategories as $category) {
            if ($category['alias'] === $selectedCategoryAlias) {
                $selectedCategoryId = (int)$category['id'];
                break;
            }
        }

        $culturalOffers = $apiClient->getCulturalOffers($page, 20, $selectedCategoryId);

        $lastPage = !empty($culturalOffers) ? ceil($culturalOffers['listSize'] / $culturalOffers['pageSize']) : 1;
        if ($page > $lastPage) {
            return $this->redirectToRoute('show_cultural_offers', ['page' => $lastPage]);
        }

        $paginationStart = $page - 5 > 0 ? ($page + 5 < $lastPage ? $page - 5 : ($lastPage - 10 > 0 ? $lastPage - 10 : 1)) : 1;
        $paginationEnd = $paginationStart + 10 <= $lastPage ? $paginationStart + 10 : $lastPage;

        return $this->render('pages/cultural_offers.html.twig', [
            'offers_categories' => $culturalOffersCategories,
            'offers' => $culturalOffers,
            'selected_category_alias' => $selectedCategoryAlias,
            'pagination_start' => $paginationStart,
            'pagination_end' => $paginationEnd,
            'last_page' => $lastPage,
            'page' => $page,
        ]);
    }

    /**
     * @Route("/oferty-kulturalne-30", methods="GET", name="show_cultural_offers_30")
     * @param Request $request
     * @param ApiClient $apiClient
     * @return Response
     */
    public function show30Action(Request $request, ApiClient $apiClient): Response
    {
        $culturalOffersCategories = $apiClient->getCulturalOffersCategories();

        $selectedCategoryAlias = !is_null($request->query->get('kategoria')) ? $request->query->get('kategoria') : 'wszystkie';

        $culturalOffers = $apiClient->getCulturalOffers(1, 30, null, '2021-02-24’T’00:00');
        if ($selectedCategoryAlias !== 'wszystkie') {
            $this->filterCulturalOffersByCategory($culturalOffers, $culturalOffersCategories, $selectedCategoryAlias);
        }
        $this->sortCulturalOffers($culturalOffers);

        return $this->render('pages/cultural_offers_30.html.twig', [
            'offers_categories' => $culturalOffersCategories,
            'offers' => $culturalOffers,
            'selected_category_alias' => $selectedCategoryAlias,
        ]);
    }

    /**
     * @param array $culturalOffers
     * @param array $culturalOffersCategories
     * @param string $selectedCategoryAlias
     */
    private function filterCulturalOffersByCategory(array &$culturalOffers, array $culturalOffersCategories, string $selectedCategoryAlias)
    {
        $selectedCategoryId = null;
        foreach ($culturalOffersCategories as $category) {
            if ($category['alias'] === $selectedCategoryAlias) {
                $selectedCategoryId = $category['id'];
                break;
            }
        }

        foreach ($culturalOffers['items'] as $key => $offer) {
            if ($offer['mainCategory'] !== $selectedCategoryId) {
                unset($culturalOffers['items'][$key]);
            }
        }
    }

    /**
     * @param array $culturalOffers
     */
    private function sortCulturalOffers(array &$culturalOffers)
    {
        foreach ($culturalOffers['items'] as &$offer) {
            usort($offer['events'], function ($a, $b) {
                if ($a['startDate'] === $b['startDate']) {
                    return 0;
                }

                return ($a['startDate'] < $b['startDate']) ? 1 : -1;
            });
        }

        usort($culturalOffers['items'], function ($a, $b) {
            if ($a['events'][0]['startDate'] === $b['events'][0]['startDate']) {
                return 0;
            }

            return ($a['events'][0]['startDate'] < $b['events'][0]['startDate']) ? 1 : -1;
        });
    }
}
