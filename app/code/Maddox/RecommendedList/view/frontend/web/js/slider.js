define([
    'jquery',
    'jquery/ui',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'slick',
], function ($, _, Component, quote, priceUtils, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Maddox_RecommendedList/slider',
            sliderWrapper: '.slider-items',
            sliderTitle: 'Recommended products',
            sliderOptions: {
                dots: true,
                infinite: true,
                slidesToShow: 3,
                slidesToScroll: 3,
                autoplay: true,
                autoplaySpeed: 2000,
                speed: 500,
                pauseOnHover: true,
                accessibility: true
            },
            itemsData: null,
            isAllowWishlist: false,
            addToCompareUrl: '',
        },

        initialize: function () {
            this._super();
        },

        slickInit: function () {
            $(this.sliderWrapper).slick(this.buildSlickConfig());
        },

        getItems: function () {
            return Object.values(this.itemsData);
        },

        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        buildSlickConfig: function () {
            return {
                ...this.sliderOptions,
                responsive: [
                    {
                        breakpoint: 1280,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2,
                            swipe: true
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            swipe: true
                        }
                    }
                ]
            }
        },
    });
});
