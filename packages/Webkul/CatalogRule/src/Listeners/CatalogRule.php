<?php

namespace Webkul\CatalogRule\Listeners;

use Webkul\CatalogRule\Repositories\CatalogRuleRepository;
use Webkul\CatalogRule\Repositories\CatalogRuleProductPriceRepository;
use Webkul\CatalogRule\Jobs\UpdateCreateCatalogRuleIndex as UpdateCreateCatalogRuleIndexJob;
use Webkul\CatalogRule\Jobs\DeleteCatalogRuleIndex as DeleteCatalogRuleIndexJob;

class CatalogRule
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected CatalogRuleRepository $catalogRuleRepository,
        protected CatalogRuleProductPriceRepository $catalogRuleProductPriceRepository
    )
    {
    }
    
    /**
     * @param  \Webkul\CatalogRule\Contracts\CatalogRule  $catalogRule
     * @return void
     */
    public function afterUpdateCreate($catalogRule)
    {
        UpdateCreateCatalogRuleIndexJob::dispatch($catalogRule);
    }
    
    /**
     * @param  integer  $catalogRuleId
     * @return void
     */
    public function beforeUpdate($catalogRuleId)
    {
        $catalogRule = $this->catalogRuleRepository->find($catalogRuleId);

        $productIds = $catalogRule->catalog_rule_products->pluck('product_id')->unique();

        $this->catalogRuleProductPriceRepository->deleteWhere(['catalog_rule_id' => $catalogRuleId]);

        DeleteCatalogRuleIndexJob::dispatch($productIds->toArray());
    }


    /**
     * @param  integer  $catalogRuleId
     * @return void
     */
    public function beforeDelete($catalogRuleId)
    {
        $catalogRule = $this->catalogRuleRepository->find($catalogRuleId);

        $productIds = $catalogRule->catalog_rule_products->pluck('product_id')->unique();

        $this->catalogRuleProductPriceRepository->deleteWhere(['catalog_rule_id' => $catalogRuleId]);

        DeleteCatalogRuleIndexJob::dispatch($productIds->toArray());
    }
}
