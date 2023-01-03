define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var mixin = {
        options: {
            recommendedByWeatherSlider: '.recommended-by-weather'
        },

        /**
         * Update [gallery-placeholder] or [product-image-photo]
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         */
        updateBaseImage: function (images, context, isInProductView) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                imagesToUpdate,
                gallery = context.find(this.options.mediaGallerySelector).data('gallery'),
                isInitial,
                isRecommendedByWeatherSlider = context.parents(this.options.recommendedByWeatherSlider).length;

            if (isInProductView) {
                if (_.isUndefined(gallery)) {
                    context.find(this.options.mediaGallerySelector).on('gallery:loaded', function () {
                        this.updateBaseImage(images, context, isInProductView);
                    }.bind(this));

                    return;
                }

                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                isInitial = _.isEqual(imagesToUpdate, initialImages);

                if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                    imagesToUpdate = imagesToUpdate.concat(initialImages);
                }

                imagesToUpdate = this._setImageIndex(imagesToUpdate);

                gallery.updateData(imagesToUpdate);
                this._addFotoramaVideoEvents(isInitial);
            } else if (isRecommendedByWeatherSlider && justAnImage && justAnImage.full) {
                context.find('.product-image-photo').attr('src', justAnImage.full);
            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        }
    }

    return function (target) {
        $.widget('mage.SwatchRenderer', target, mixin);

        return $.mage.SwatchRenderer;
    }
});
