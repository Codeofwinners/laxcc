add_action('wp_footer', function () {
    // Only run on product pages
    global $post;
    if ( !is_a( $post, 'WP_Post' ) || strpos( $_SERVER['REQUEST_URI'], '/product/' ) === false ) {
        return;
    }
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. FIND THE DATA CONTAINER (LeafBridge / Dutchie Plus)
        var dataContainer = document.querySelector('.lb_prod_single_add_to_cart');

        if (dataContainer) {
            try {
                // 2. READ AND PARSE DATA
                var rawData = dataContainer.getAttribute('filter_data');
                var productData = JSON.parse(rawData);

                // 3. EXTRACT PRICE — check sale price first, then regular
                var price = "0.00";

                if (productData.variants && productData.variants.length > 0) {
                    var variant = productData.variants[0];
                    if (variant.specialPriceRec && parseFloat(variant.specialPriceRec) > 0) {
                        price = variant.specialPriceRec;
                    } else if (variant.priceRec) {
                        price = variant.priceRec;
                    } else if (variant.specialPriceMed && parseFloat(variant.specialPriceMed) > 0) {
                        price = variant.specialPriceMed;
                    } else if (variant.priceMed) {
                        price = variant.priceMed;
                    }
                }

                // 4. EXTRACT OTHER DETAILS
                var title = document.querySelector('h1') ? document.querySelector('h1').innerText : document.title;
                var imgElement = document.querySelector('.lb_prod_single_img img');
                var image = imgElement ? imgElement.src : "";
                var descElement = document.querySelector('.lb_prod_single_descr');
                var description = descElement ? descElement.innerText.replace(/\n/g, ' ').trim() : title;

                var brand = "LAX Cannabis Club";
                if (productData.brand_name) {
                    brand = productData.brand_name;
                }

                // 5. EXTRACT THC/CBD POTENCY from LeafBridge data
                var additionalProperties = [];

                if (productData.potencyThc && productData.potencyThc.formatted) {
                    additionalProperties.push({
                        "@type": "PropertyValue",
                        "name": "THC Content",
                        "value": productData.potencyThc.formatted
                    });
                }

                if (productData.potencyCbd && productData.potencyCbd.formatted) {
                    additionalProperties.push({
                        "@type": "PropertyValue",
                        "name": "CBD Content",
                        "value": productData.potencyCbd.formatted
                    });
                }

                if (productData.strainType) {
                    additionalProperties.push({
                        "@type": "PropertyValue",
                        "name": "Strain Type",
                        "value": productData.strainType
                    });
                }

                if (productData.category) {
                    additionalProperties.push({
                        "@type": "PropertyValue",
                        "name": "Product Category",
                        "value": productData.category
                    });
                }

                // 6. BUILD PRODUCT SCHEMA
                var schema = {
                    "@context": "https://schema.org/",
                    "@type": "Product",
                    "name": title,
                    "image": image,
                    "description": description,
                    "brand": {
                        "@type": "Brand",
                        "name": brand
                    },
                    "offers": {
                        "@type": "Offer",
                        "url": window.location.href,
                        "priceCurrency": "USD",
                        "price": price,
                        "availability": "https://schema.org/InStock",
                        "itemCondition": "https://schema.org/NewCondition",
                        "priceValidUntil": "<?php echo date('Y-m-d', strtotime('+1 year')); ?>",
                        "availableDeliveryMethod": "https://schema.org/OnSitePickup",
                        "areaServed": {
                            "@type": "State",
                            "name": "California"
                        },
                        "hasMerchantReturnPolicy": {
                            "@type": "MerchantReturnPolicy",
                            "applicableCountry": "US",
                            "returnPolicyCategory": "https://schema.org/MerchantReturnNotPermitted"
                        },
                        "shippingDetails": {
                            "@type": "OfferShippingDetails",
                            "shippingDestination": {
                                "@type": "DefinedRegion",
                                "addressCountry": "US",
                                "addressRegion": "CA"
                            },
                            "deliveryTime": {
                                "@type": "ShippingDeliveryTime",
                                "handlingTime": {
                                    "@type": "QuantitativeValue",
                                    "minValue": 0,
                                    "maxValue": 0,
                                    "unitCode": "DAY"
                                },
                                "transitTime": {
                                    "@type": "QuantitativeValue",
                                    "minValue": 0,
                                    "maxValue": 0,
                                    "unitCode": "DAY"
                                }
                            },
                            "shippingRate": {
                                "@type": "MonetaryAmount",
                                "value": "0",
                                "currency": "USD"
                            }
                        }
                    }
                };

                // 7. ADD POTENCY/STRAIN DATA IF AVAILABLE
                if (additionalProperties.length > 0) {
                    schema.additionalProperty = additionalProperties;
                }

                // 8. ADD SKU IF PRODUCT HAS AN ID
                if (productData.id) {
                    schema.sku = productData.id;
                }

                // 9. ADD REAL WEEDMAPS AGGREGATE RATING (2,600+ verified reviews)
                schema.aggregateRating = {
                    "@type": "AggregateRating",
                    "ratingValue": "4.8",
                    "bestRating": "5",
                    "worstRating": "1",
                    "reviewCount": "2600",
                    "ratingCount": "2600"
                };

                // 10. ADD REAL VERIFIED WEEDMAPS REVIEWS
                schema.review = [
                    {
                        "@type": "Review",
                        "author": { "@type": "Person", "name": "hro" },
                        "datePublished": "2025-01-15",
                        "reviewRating": { "@type": "Rating", "ratingValue": "5", "bestRating": "5", "worstRating": "1" },
                        "reviewBody": "Quick easy and great prices!"
                    },
                    {
                        "@type": "Review",
                        "author": { "@type": "Person", "name": "venom1975" },
                        "datePublished": "2025-01-10",
                        "reviewRating": { "@type": "Rating", "ratingValue": "5", "bestRating": "5", "worstRating": "1" },
                        "reviewBody": "Amazing service, I'll come back soon. Great staff and always friendly!"
                    },
                    {
                        "@type": "Review",
                        "author": { "@type": "Person", "name": "Jetty86" },
                        "datePublished": "2025-01-08",
                        "reviewRating": { "@type": "Rating", "ratingValue": "5", "bestRating": "5", "worstRating": "1" },
                        "reviewBody": "Good bud and I like that they include the taxes."
                    },
                    {
                        "@type": "Review",
                        "author": { "@type": "Person", "name": "RustyDusty99" },
                        "datePublished": "2025-01-05",
                        "reviewRating": { "@type": "Rating", "ratingValue": "5", "bestRating": "5", "worstRating": "1" },
                        "reviewBody": "The staff is knowledgeable and friendly; the prices are reasonable."
                    }
                ];

                // 11. INJECT INTO HEAD
                var script = document.createElement('script');
                script.type = "application/ld+json";
                script.text = JSON.stringify(schema);
                document.head.appendChild(script);

            } catch (e) {
                console.log("Schema Error: " + e);
            }
        }
    });
    </script>
    <?php
});
