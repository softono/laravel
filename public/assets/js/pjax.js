/**
 * @typedef {Object} Pjax
 * @property {jQuery} $mainContainer - jQuery reference to main container
 * @property {Array} cache - Cached pages for faster navigation
 * @description Client-side pjax implementation for handling SPA navigation and partial page loads
 */

const pjax = {
    /** @type {jQuery} jQuery reference to main container */
    $mainContainer: null,
    /**
     * Load page content via AJAX with partial rendering
     * @param {string} url - The URL to load content from
     * @param {boolean} [cache=false] - Whether to cache the page content
     * @param {boolean} [scroll=true] - Whether to scroll to top
     */
    loadPage(url, cache = false, scroll = true) {
        if (url != window.location.href) {
            window.history.pushState({}, "", url);
        }
        //cache code
        const cachedPage = AppCache.get(url);
        if (cachedPage) {
            this.updateContent(cachedPage);
            if (!scroll) $(window).scrollTop(0);
            if (cache) { return false; }
        } else {
            this.$mainContainer.css("min-height", this.$mainContainer.height()).html('<div class="loading-text">Loading...</div>');
        }

        const ajaxUrl = `${url}${url.includes("?") ? "&" : "?"}partial=1&layout=${this.$mainContainer.data("layout")}`;

        $.ajax({
            xhrFields: {
                withCredentials: true
            },
            url: ajaxUrl,
            method: "GET",
            success: (response) => {
                if (response === "unauthorized") {
                    window.location.reload();
                } else if (response === "reload" || response.includes("<body")) {
                    window.location.href = url;
                } else {
                    //cache start
                    if (cachedPage == response) {
                        return false;
                    }
                    AppCache.set(url, response);
                    //cache end

                    this.updateContent(response, scroll);
                    if (!scroll) $(window).scrollTop(0);
                }
            },
            error: (xhr) => {
                try {
                    const response = JSON.parse(xhr.responseText);
                    this.$mainContainer.html(response.message || "");
                } catch {
                    window.location.href = url;
                }
            }
        });
    },

    /**
     * Update content in main container
     * @param {string} html - The HTML content to update
     * @param {boolean} scroll - Whether to scroll to top
     */
    updateContent(html) {
        this.$mainContainer.html(html).css("min-height", 0);
        $("title").text($("#main-content").data("title"));
        runDocumentReady();
    },

    /**
     * Set up click handlers for pjax-enabled links
     */
    routeLinks() {
        $(document).on("click", "a.pjax", (e) => {
            const target = e.currentTarget;
            const href = target.href;

            if (!href || href.match(/#|javascript:void|undefined/)) return;
            if (e.ctrlKey || target.target === "_blank") return window.open(href, "_blank");

            if (target.hasAttribute("data-pjax-layout")) {
                const layout = target.hasAttribute("data-pjax-cache");
                if (layout !== pjax.$mainContainer.data("layout")) {
                    return;
                }
            }

            e.preventDefault();

            const scroll = target.getAttribute("data-pjax-scroll") !== "false";
            const cache = target.hasAttribute("data-pjax-cache");

            window.history.pushState({}, '', href);

            this.loadPage(href, cache, scroll);
            this.updateActiveMenu(href);
        });
    },

    /**
     * Update link class based on the current URL
     * @param {string} url - The URL to check against
     */
    activeMenuList: '',
    updateActiveMenu(url) {
        this.activeMenuList.removeClass("active open");
        let pjaxurl = url.replace(APP_URL, '').split('?')[0];
        if (pjaxurl == '') { pjaxurl = 'home'; }
        this.activeMenuList.each(function (index, element) {
            element = $(element);
            // Get the data-active_menu_links attribute, split  each entry
            let pjaxLinks = [];
            if (element.data('active_menu_links')) {
                pjaxLinks = pjaxLinks.concat(element.data('active_menu_links').split(','));
            }
            // If no links found, check for nested .active-menu children
            if (!pjaxLinks.length) {
                element.find('.active-menu').each(function (i, childElement) {
                    if ($(childElement).data('active_menu_links')) {
                        pjaxLinks = pjaxLinks.concat($(childElement).data('active_menu_links').split(','));
                    }
                });
            }
            if (pjaxLinks.includes(pjaxurl)) {
                element.addClass("active");
                const extraClass = element.data('active_menu_class');
                if (extraClass) {
                    element.addClass(extraClass);
                }
            }
        });
    },


    /**
     * Initialize pjax functionality
     */
    init() {
        this.$mainContainer = $("#main-container");
        if (!this.$mainContainer.length) return console.error("pjax: Main container not found");
        this.routeLinks();
        window.addEventListener("popstate", () => this.loadPage(window.location.href));

        this.activeMenuList = $('.active-menu');
        this.updateActiveMenu(window.location.href);

        runDocumentReady();
    }
};

/**
 * AppCache class for storing and retrieving page data in internal storage.
 */
class AppCache {
    static cacheEnabled = true;

    // Custom in-memory storage
    static storage = {};

    /**
     * Encode a key to Base64 format.
     */
    static encodeKey(key) {
        return btoa(key.replace($("base").attr("href")));
    }

    /**
     * Save string value in AppCache.storage
     */
    static set(key, value) {
        if (!AppCache.cacheEnabled) return false;
        const encodedKey = this.encodeKey(key);

        try {
            AppCache.storage[`cache_${encodedKey}`] = value;
        } catch (e) {
            AppCache.clear();
        }
    }

    /**
     * Save JSON data in AppCache.storage
     */
    static setData(key, data) {
        if (!AppCache.cacheEnabled) return false;
        const encodedKey = this.encodeKey(key);

        try {
            AppCache.storage[`cache_${encodedKey}`] = JSON.stringify(data);
        } catch (e) {

        }
    }

    /**
     * Retrieve string data
     */
    static get(key) {
        if (!AppCache.cacheEnabled) return false;
        const encodedKey = this.encodeKey(key);

        return AppCache.storage[`cache_${encodedKey}`] || null;
    }

    /**
     * Retrieve JSON data
     */
    static getData(key) {
        if (!AppCache.cacheEnabled) return false;
        const encodedKey = this.encodeKey(key);

        let cachedData = AppCache.storage[`cache_${encodedKey}`];
        return cachedData ? JSON.parse(cachedData) : null;
    }

    /**
     * Remove specific item
     */
    static remove(key) {
        if (!AppCache.cacheEnabled) return false;
        const encodedKey = this.encodeKey(key);

        delete AppCache.storage[`cache_${encodedKey}`];
    }

    /**
     * Clear all cached data
     */
    static clear() {
        AppCache.storage = {};
    }
}


/**
 * Executes all registered document-ready functions.
 */
function runDocumentReady() {
    if (documentReadyFunctions) {
        let oldDocumentReadyFunctions = documentReadyFunctions;
        documentReadyFunctions = [];
        $.each(oldDocumentReadyFunctions, function (index, cb) {
            try {
                cb();
            } catch (e) {
                console.error(e);
            }
        });
    }
}
/**
 * Initialize pjax when DOM is ready
 * @listens DOMContentLoaded
 */
$(document).ready(() => pjax.init());