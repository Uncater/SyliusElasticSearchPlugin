<?php

declare(strict_types=1);

namespace Sylius\ElasticSearchPlugin\Factory\Document;

use Doctrine\Common\Collections\ArrayCollection as Collection;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;
use Sylius\ElasticSearchPlugin\Document\ImageDocument;
use Sylius\ElasticSearchPlugin\Document\TaxonDocument;

final class TaxonDocumentFactory implements TaxonDocumentFactoryInterface
{
    /** @var string */
    private $taxonDocumentClass;

    /** @var ImageDocumentFactoryInterface */
    private $imageDocumentFactory;

    public function __construct(string $taxonDocumentClass, ImageDocumentFactoryInterface $imageDocumentFactory)
    {
        $this->taxonDocumentClass = $taxonDocumentClass;
        $this->imageDocumentFactory = $imageDocumentFactory;
    }

    /**
     * @param TaxonInterface $taxon Sylius taxon model
     * @param LocaleInterface $localeCode
     * @param int|null $position Override the position in the Taxon model by passing your own
     *
     * @return TaxonDocument
     */
    public function create(TaxonInterface $taxon, LocaleInterface $localeCode, ?int $position = null): TaxonDocument
    {
        /** @var TaxonTranslationInterface $taxonTranslation */
        $taxonTranslation = $taxon->getTranslation($localeCode->getCode());

        /** @var TaxonDocument $taxonDocument */
        $taxonDocument = new $this->taxonDocumentClass();
        $taxonDocument->setCode($taxon->getCode());
        $taxonDocument->setSlug($taxonTranslation->getSlug());
        if (is_int($position)) {
            $taxonDocument->setPosition($position);
        } else {
            $taxonDocument->setPosition($taxon->getPosition());
        }

        $taxonDocument->setDescription($taxonTranslation->getDescription());

        /** @var ImageDocument[] $images */
        $images = [];
        foreach ($taxon->getImages() as $image) {
            $images[] = $this->imageDocumentFactory->create($image);
        }
        $taxonDocument->setImages(new Collection($images));

        return $taxonDocument;
    }
}
