// const pjax={
//     loadPage:function(url){window.location.href=url;},
// }
// Extend the String prototype with a replaceAll function
String.prototype.replaceAll = function (search, replacement) {
    return this.replace(new RegExp(search, "g"), replacement);
};

/**
 * @namespace app
 * @description Core application utility functions for handling AJAX, UI interactions, and data manipulation
 */
const app = {
    /**
     * Runs a follow-up action. The view decides what happens after a successful request
     * with `data-next` (and `data-next-url`) on the element that triggered it; the
     * server response never carries navigation.
     * @param {string} next - load | refresh | list_refresh | table_refresh | redirect | reload | hide_modal | show_modal_view
     * @param {string} [url] - Target for load, redirect and show_modal_view
     */
    runNextAction: function (next, url) {
        if (next === "load") {
            pjax.loadPage(url);
        } else if (next === "refresh") {
            pjax.loadPage(window.location.href);
        } else if (next === "list_refresh") {
            pagination.loadData(false, false);
        } else if (next === "table_refresh") {
            datatableObj.ajax.reload();
        } else if (next === "redirect") {
            window.location.href = url;
        } else if (next === "reload") {
            window.location.reload();
        } else if (next === "hide_modal") {
            this.hideModal();
        } else if (next === "show_modal_view") {
            this.showModalView(url);
        }
    },

    /** The follow-up declared on an element: data-next="load" data-next-url="...". */
    followUpOf: function ($el) {
        return { next: $el.data("next"), url: $el.data("next-url") };
    },

    /**
     * Shows a modal view loaded from a URL
     * @param {string} url - URL to fetch modal content
     */
    showModalView: function (url) {
        //cache code
        const cachedPage = AppCache.get(url);
        if (cachedPage) {
            this.setModalContent(cachedPage);
            this.showModal();
            runDocumentReady();
        } else {
            this.showLoading();
        }

        $.ajax({
            url,
            method: "GET",
            success: (response) => {
                //cache start
                if (cachedPage && cachedPage == response) {
                    return false;
                }
                AppCache.set(url, response);
                //cache end

                this.hideLoading();
                this.setModalContent(response);
                this.showModal();
                // Run any necessary initializationf
                runDocumentReady();
            },
            error: this.ajaxError,
        });
    },

    /**
     * Modal management functions
     */
    setModalContent: function (html) {
        this.commonModel.find("#common-modal-content").html(html);
    },

    showModal: function () {
        if (!this.commonModel.hasClass("modal-open")) {
            app.openModal(this.commonModel);
        }
    },

    hideModal: function () {
        if (this.commonModel.hasClass("modal-open")) {
            app.closeModal(this.commonModel);
        }
    },

    /**
     * Generic Tailwind modal open/close helpers (replaces Bootstrap's .modal() plugin)
     */
    openModal: function ($modal) {
        $modal = $modal.jquery ? $modal : $($modal);
        $modal.removeClass("hidden").addClass("modal-open");
        $("body").addClass("overflow-hidden");
    },

    closeModal: function ($modal) {
        $modal = $modal.jquery ? $modal : $($modal);
        $modal.removeClass("modal-open").addClass("hidden");
        if ($(".modal.modal-open").length === 0) {
            $("body").removeClass("overflow-hidden");
        }
    },

    /**
     * Performs an AJAX action without confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    ajaxAction: function (obj, cb) {
        const $obj = $(obj);
        const postData = {};

        if ($obj.data("id")) {
            postData.id = $obj.data("id");
        }

        this.ajaxPost($obj.data("action"), postData, cb);
    },

    /**
     * Performs an AJAX action with confirmation
     * @param {HTMLElement} obj - DOM element with data attributes
     * @param {Function} cb - Callback function
     */
    confirmAction: function (obj, cb) {
        const $obj = $(obj);
        const postData = $obj.data("id") ? { id: $obj.data("id") } : {};
        const followUp = this.followUpOf($obj);
        this.ajaxConfirm($obj.data("action"), postData, cb ?? ((response) => app.ajaxSuccess(response, followUp)));
    },

    /**
     * Shows a confirmation dialog before performing AJAX POST
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxConfirm: function (url, postData, cb) {
        app
            .showConfirmationPopup({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-secondary",
                },
            })
            .then((result) => {
                if (result) {
                    this.ajaxPost(url, postData, cb);
                }
            });
    },

    /**
     * Server-side DataTable with the Tailwind styling integration. Sets the global
     * `datatableObj` that the `table_refresh` follow-up reloads.
     * @param {string} selector - Table element
     * @param {{url: string, columns: Object[], method?: string, order?: Array}} options
     */
    dataTable: function (selector, { url, columns, method = "post", order = [[0, "desc"]] }) {
        datatableObj = $(selector).DataTable({
            ajax: dataTableAjax({ url, method }),
            columns,
            order,
            responsive: true,
            serverSide: true,
        });
        return datatableObj;
    },

    /**
     * AJAX utility functions
     */
    ajaxPost: function (url, postData, cb) {
        postData[CSRF_NAME] = CSRF_TOKEN;
        this.ajaxRequest(url, postData, cb);
    },

    ajaxGet: function (url, cb = this.ajaxSuccess) {
        this.showLoading();
        $.ajax({
            url,
            method: "GET",
            dataType: "json",
            success: (response) => {
                this.hideLoading();
                cb(response);
            },
            error: this.ajaxError,
        });
    },

    /**
     * Handles form submission via AJAX
     * @param {HTMLFormElement} form - Form element to submit
     * @param {Function} cb - Callback function
     */
    currentAjaxForm:false,
    ajaxForm: function (form, cb) {
        this.currentAjaxForm = $(form);
        const followUp = this.followUpOf(this.currentAjaxForm);
        this.ajaxRequest(
            this.currentAjaxForm.attr("action"),
            this.currentAjaxForm.serialize(),
            cb ?? ((response) => app.ajaxSuccess(response, followUp))
        );
    },

    /**
     * Handles file upload form submission
     * @param {HTMLFormElement} form - Form element with files
     * @param {Function} cb - Callback function
     */
    ajaxFileForm: function (form, cb) {
        const $form = $(form);
        const followUp = this.followUpOf($form);
        this.ajaxFileRequest(
            $form.attr("action"),
            new FormData($form[0]),
            cb ?? ((response) => app.ajaxSuccess(response, followUp))
        );
    },
    ajaxFilePost: function (url, postData, cb) {
        postData.append(CSRF_NAME, CSRF_TOKEN);
        app.ajaxFileRequest(url, postData, cb);
    },

    /**
     * Core AJAX request handler
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxRequest: function (url, postData, cb = this.ajaxSuccess) {
        this.showLoading();
        $.ajax({
            url,
            method: "POST",
            data: postData,
            dataType: "json",
            success: (response) => {
                this.hideLoading();
                cb(response);
            },
            error: this.ajaxError,
        });
    },

    /**
     * Core AJAX request handler
     * @param {string} url - Target URL
     * @param {Object} postData - Data to send
     * @param {Function} cb - Callback function
     */
    ajaxFileRequest: function (url, postData, cb) {
        if (cb === undefined) {
            cb = app.ajaxSuccess;
        }
        app.showLoading();
        $.ajax({
            url: url,
            method: "post",
            data: postData,
            dataType: "json",
            success: function (response) {
                app.hideLoading();
                cb(response);
            },
            error: app.ajaxError,
            processData: false,
            contentType: false,
        });
    },

    nextAction: function (followUp) {
        if (!followUp || typeof followUp !== "object" || !followUp.next) {
            return;
        }
        String(followUp.next)
            .split(",")
            .forEach(function (next) {
                app.runNextAction(next.trim(), followUp.url);
            });
    },

    /**
     * Default AJAX success handler: shows the message, then runs the follow-up the
     * view declared for the request (see followUpOf).
     * @param {Object} response - {status, message, data}
     * @param {{next?: string, url?: string}} [followUp]
     */
    ajaxSuccess: function (response, followUp) {
        app.hideLoading();
        if (response.status) {
            if (response.message) {
                app.showMessage(response.message, "success");
                setTimeout(function () {
                    app.nextAction(followUp);
                }, 2000);
            } else {
                app.nextAction(followUp);
            }
        } else if (response.message) {
            app.showMessage(response.message, "error");
        }
    },

    /**
     * Default AJAX error handler
     */
    ajaxError: function (e) {
        app.hideLoading();
        app.showMessage(e.responseJSON?.message ?? "Something went wrong. Please try again later.", "error");
    },

    /**
     * Loading indicator management
     */
    showLoading: function () {
        //Swal.showLoading();
        $("#common-loader").show();
    },

    hideLoading: function () {
        // Swal.close();
        $("#common-loader").hide();
    },

    showMessage: function (message, type) {
        var toastHtml = `
        <div class="toast-msg toast-__type__" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-msg-header">
                <div class="flex items-center gap-2">
                    <i class="bx bx-bell"></i>
                    <span class="font-medium">__title__</span>
                </div>
                <button type="button" class="toast-dismiss" aria-label="Close"><i class="bx bx-x text-lg"></i></button>
            </div>
            <div class="toast-msg-body">__message__</div>
        </div>`;
        var title = type.charAt(0).toUpperCase() + type.slice(1);
        type = type.replace("error", "danger");
        $("#common-toast").html(
            app.dataToHtml(toastHtml, { message: message, title: title, type: type })
        );
        setTimeout(function () {
            $("#common-toast").html("");
        }, 5000);
        // Swal.fire(title,message,type);
    },
    showMessageWithCallback: function (message, type) {
        app.showMessage(message, type);
        return new Promise((resolve, reject) => {
            setTimeout(function () {
                resolve(true);
            }, 2000);
        });
    },
    showConfirmationPopup: function (params) {
        return new Promise((resolve, reject) => {
            if (confirm(params.text)) {
                resolve(true);
            } else {
                reject(false);
            }
            // Swal.fire(params).then((result) => {
            //     if (result.value) {
            //         resolve(true);
            //     }else{
            //         reject(false);
            //     }
            // });
        });
    },

    /**
     * Replaces template placeholders with actual data values
     * @param {string} htmlString - Template string containing placeholders like __key__
     * @param {Object} data - Key-value pairs for replacement
     * @returns {string} Processed HTML string with replacements
     */
    dataToHtml: function (htmlString, data) {
        // Replace all known placeholders with values
        Object.entries(data).forEach(([key, value]) => {
            htmlString = htmlString.replaceAll(`__${key}__`, value);
        });
        // Clean up any remaining placeholders
        return htmlString.replaceAll(/\__(.+?)\__/g, "");
    },

    /**
     * Renders an HTML template with multiple data entries
     * @param {string} template - HTML template string
     * @param {Array} data - Array of data objects to render
     * @returns {string} Combined HTML string
     */
    renderHtmlData: function (template, data) {
        if (!template) return "";
        return data.reduce(
            (html, item) => html + this.dataToHtml(template, item),
            ""
        );
    },

    /**
     * Resource loading utilities
     */
    addCSS: function (urls) {
        urls.forEach((url) => {
            if (!$(`link[href="${url}"]`).length) {
                $("body").append(`<link href="${url}" rel="stylesheet">`);
            }
        });
    },

    addJS: function (urls) {
        urls.forEach((url) => {
            if (!$(`script[src="${url}"]`).length) {
                $("body").append(`<script src="${url}"></script>`);
            }
        });
    },

    /**
     * Loads a script and executes callback when ready
     * @param {string} url - Script URL
     * @param {Function} callback - Callback function
     */
    loadScript: function (url, callback) {
        if ($(`script[src="${url}"]`).length) {
            callback();
            return;
        }

        const script = document.createElement("script");
        script.type = "text/javascript";
        script.src = url;

        if (script.readyState) {
            script.onreadystatechange = function () {
                if (
                    script.readyState === "loaded" ||
                    script.readyState === "complete"
                ) {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {
            script.onload = callback;
        }

        document.head.appendChild(script);
    },
    
    /**
    * Sets the browser's URL and optionally updates query parameters.
    * @param {string} url - The base URL to set.
    * @param {Object} params - Key-value pairs to add or update in the URL's query string.
    */
    setUrl: function (url, params) {
        if (params) {
          const paramStringArray = [];
          let [baseUrl, queryString] = url.split("?");
        
          if (queryString) {
            const existingParams = queryString.split("&");
            existingParams.forEach((param) => {
              const [key, value] = param.split("=");
              if (!params.hasOwnProperty(key)) {
                paramStringArray.push(`${key}=${value}`);
              }
            });
          }
        
          Object.entries(params).forEach(([key, value]) => {
            paramStringArray.push(`${key}=${value}`);
          });
        
          url = `${baseUrl}?${paramStringArray.join("&")}`;
        }
        
        window.history.pushState({}, "", url);
    },


    /**
     * Cookie management utilities
     */
    setCookie: function (cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
        const expires = `expires=${d.toUTCString()}`;
        document.cookie = `${cname}=${cvalue}; ${expires};path=/`;
    },

    getCookie: function (cname) {
        const name = `${cname}=`;
        const decodedCookie = decodeURIComponent(document.cookie);
        const ca = decodedCookie.split(";");

        for (let c of ca) {
            while (c.charAt(0) === " ") {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    },

    validateFile: function (
        file,
        allowedExtensions = ["png", "jpg", "jpeg", "webp", "gif"]
    ) {
        if (!file || !file.name) {
            return { status: 0, message: "No file provided" };
        }

        // Convert allowed extensions array to a regex
        var pattern =
            "\\.(" +
            allowedExtensions.map((ext) => ext.replace(".", "")).join("|") +
            ")$";
        var re = new RegExp(pattern, "i");

        if (!re.test(file.name)) {
            return { status: 0, message: "File type is not allowed" };
        }

        return { status: 1, message: "" };
    },

    /**
     * Generates a random ID string
     * @param {number} length - Length of ID to generate
     * @returns {string} Random ID
     */
    makeId: function (length) {
        const characters =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        return Array.from({ length }, () =>
            characters.charAt(Math.floor(Math.random() * characters.length))
        ).join("");
    },

    /**
     * Initializes the application
     */
    init: function () {
        this.commonModel = $("#common-modal");

        // Set up user token if not exists
        if (!this.getCookie(`${APP_UID}_token`)) {
            this.setCookie(`${APP_UID}_token`, this.makeId(64), 365);
        }

        // Set timezone
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (this.getCookie(`${APP_UID}_tz`) !== tz) {
            this.setCookie(`${APP_UID}_tz`, tz, 30);
        }
    },
};

$(document).ready(function () {
    app.init();

    // Generic modal triggers/dismissals (replaces Bootstrap's data-bs-toggle="modal")
    $(document).on("click", "[data-modal-open]", function (e) {
        e.preventDefault();
        app.openModal($($(this).data("modal-open")));
    });
    $(document).on("click", "[data-modal-dismiss]", function (e) {
        e.preventDefault();
        app.closeModal($(this).closest(".modal"));
    });
    $(document).on("click", ".modal", function (e) {
        if ($(e.target).is(".modal")) {
            app.closeModal($(this));
        }
    });
    $(document).on("click", ".toast-dismiss", function () {
        $(this).closest(".toast-msg").remove();
    });
    $(document).on("click", ".alert-dismiss", function () {
        $(this).closest(".alert").remove();
    });
    $(document).on("click", ".menu-toggle", function (e) {
        e.preventDefault();
        $(this).closest(".menu-item").toggleClass("open");
    });
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            $(".modal.modal-open").each(function () {
                app.closeModal($(this));
            });
        }
    });
});




/**
 * A class for managing dynamic content loading and interactions with a target HTML element.
 */
class View {
    /**
     * Target element where content will be dynamically loaded.
     * @type {string}
     */
    target = "";
    /**
     * Initializes the View class with a target element.
     * @param {string} target - Selector for the target element.
     */
    init(target) {
        this.target = $(target);
    }
    /**
     * Clears the content of the target element.
     */
    clear() {
        this.target.html("");
    }
    /**
     * Loads content into the target element via an AJAX GET request.
     * @param {string} url - The URL to fetch the content from.
     */
    load(url) {
        var target = this.target;
        const cachedPage = AppCache.get(url);
        if (cachedPage) {
            this.setModalContent(response);
            this.showModal();
            runDocumentReady();
        } else {
            target.html('<div class="loading-text">Loading...</div>');
        }
        $.ajax({
            url: url,
            method: "get",
            success: function (response) {
                //cache start
                if (cachedPage === response) {
                    return false;
                }
                AppCache.set(url, response);
                //cache end
                target.html(response);
                runDocumentReady();
            },
            error: function (e) {
                app.showMessage(
                    "Something went wrong. Pelase Try after sometime.",
                    "error"
                );
            },
        });
    }
}

/**
 * A class for managing AJAX-based pagination functionality.
 */
class Pagination {
    /**
     * URL for AJAX requests.
     * @type {string}
     */
    ajaxUrl = "";
    /**
     * jQuery object representing the container for pagination content.
     * @type {jQuery}
     */
    ajaxContainer = false;
    /**
     * Determines whether to load data on initialization.
     * @type {boolean}
     */
    initLoadData = true;
    /**
     * Pagination type (e.g., normal or load-more).
     * @type {number}
     */
    type = 1; // 1: link pagination, 2: load more, 3: load old , 4: scroll
    /**
     * Data sent with AJAX requests.
     * @type {object}
     */
    postData = {
        page: 1,
        _token: CSRF_TOKEN,
        sort: { field: "", direction: "asc" },
        search: "",
        filter: {},
        filter_extra: {},
    };

    /**
     * Initializes the pagination with URL and container ID.
     * @param {string} url - The URL for AJAX requests.
     * @param {string} [ajaxContainerId] - The container for displaying pagination.
     */
    init(url, ajaxContainerId) {
        if (ajaxContainerId === undefined) {
            ajaxContainerId = "#pagination-ajax-container";
        }
        this.ajaxUrl = url;
        this.ajaxContainer = $(ajaxContainerId);

        var urlParam = new URLSearchParams(new URL(window.location.href).search);
        this.postData.filter = urlParam.get("filter");
        this.postData.page = urlParam.get("page");
        if (this.postData.filter == "") {
            this.postData.filter = {};
        } else if (typeof this.postData.filter == "object") {
        } else {
            this.postData.filter = this.postData.filter.replaceAll("&quot;", '"');
            this.postData.filter = JSON.parse(this.postData.filter);
        }
        if (this.initLoadData) {
            this.loadData();
        } else {
            var _this = this;
            _this.ajaxContainer.find(".page-link").click(function () {
                _this.loadList($(this).data("page"));
            });
        }
    }

    /**
     * Initializes the pagination functionality by setting the URL for AJAX requests
     * and the container where the paginated data will be loaded.
     * Then, it triggers the data loading process.
     *
     * @param {string} url - The URL for AJAX requests to fetch paginated data.
     * @param {string} ajaxContainer - The selector or DOM element where the data will be injected.
     */
    initPagination(url, ajaxContainer) {
        // Set the AJAX URL and container for future use
        this.ajaxUrl = url;
        this.ajaxContainer = $(ajaxContainer);
        // Load the data from the provided URL
        this.loadData();
    }
    /**
     * Loads data via an AJAX request and updates the container.
     */
    loadData(cacheEnabled = true, showLoading = true) {
        var _this = this;

        //cache start
        const cacheKey = _this.ajaxUrl + "|" + JSON.stringify(_this.postData);
        let cachedPage = false;
        if (cacheEnabled) {
            cachedPage = AppCache.get(cacheKey);
            if (cachedPage) {
                if (_this.type == 1) {
                    _this.ajaxContainer.html(cachedPage);
                } else {
                    _this.ajaxContainer.find(".pagination-load-more").remove();
                    _this.ajaxContainer.append(cachedPage);
                }
                _this.ajaxContainer.find(".page-link").click(function () {
                    _this.loadList($(this).data("page"));
                });
                runDocumentReady();
                scrollToTop();
                showLoading = false;
            }
        }
        //cache end
        if (showLoading) {
            if (_this.type == 1) {
                _this.ajaxContainer.css("min-height", _this.ajaxContainer.height());
                _this.ajaxContainer.html('<div class="loading-text">Loading...</div>');
            } else {
                _this.ajaxContainer
                    .find(".page-link")
                    .text('<div class="loading-text">Loading...</div>');
            }
        }
        $.ajax({
            method: "post",
            url: this.ajaxUrl,
            data: this.postData,
            success: function (response) {
                //cache start
                if (cachedPage === response) {
                    return false;
                }
                AppCache.set(cacheKey, response);
                //cache end
                if (_this.type == 1) {
                    _this.ajaxContainer.html(response);
                    _this.ajaxContainer.css("min-height", 0);
                } else {
                    _this.ajaxContainer.find(".pagination-load-more").remove();
                    _this.ajaxContainer.append(response);
                }
                _this.ajaxContainer.find(".page-link").click(function () {
                    _this.loadList($(this).data("page"));
                });
                runDocumentReady();
                scrollToTop();
            },
            error: function (e) {
                _this.ajaxContainer.html(e.message);
            },
        });
    }
    /**
     * Loads the list of data for the specified page.
     *
     * @param {number} page - The page number to load.
     */
    loadList(page) {
        this.postData.page = page;
        this.loadData(); // Fetch the data based on updated page
        app.setUrl(window.location.href, { page: page }); // Update the URL to reflect the page number
    }
    /**
     * Sorts the data by the specified field and direction.
     *
     * @param {string} field - The field by which to sort the data.
     * @param {string} direction - The direction to sort the data (e.g., 'asc' or 'desc').
     */
    sort(field, direction) {
        this.postData.sort.field = field;
        this.postData.sort.direction = direction;
        this.loadList(1); // Reset to the first page after sorting
        app.setUrl(window.location.href, {
            page: 1,
            sort: field + "-" + direction,
        }); // Update the URL with sort parameters
    }
    /**
     * Searches the data based on the provided search value.
     *
     * @param {string} value - The search value to filter the data by.
     */
    search(value) {
        this.postData.search = value;
        this.loadList(1); // Reset to the first page after search
        app.setUrl(window.location.href, { page: 1, search: value }); // Update the URL with search parameters
    }

    filterClear() {
        this.postData.filter = {};
        this.setUrl(window.location.href, {
            page: 1,
            filter: "",
        });
    }
    /**
     * Filters the data based on a key-value pair. If the value is empty or the same as the current filter, it removes the filter.
     *
     * @param {string} key - The key to filter the data by.
     * @param {string} value - The value to filter the data by.
     */
    filter(key, value) {
        if (value == "" || this.postData.filter[key] == value) {
            delete this.postData.filter[key]; // Remove filter if value is empty or matches the existing one
        } else {
            this.postData.filter[key] = value; // Add or update the filter with the new value
        }
        this.loadList(1); // Reset to the first page after filtering
        app.setUrl(window.location.href, {
            page: 1,
            filter: JSON.stringify(this.postData.filter), // Update the URL with the current filters
        });
    }
    /**
     * Handles filtering for multiple values. It adds or removes values from the filter based on whether they are already included.
     *
     * @param {string} key - The key to filter the data by.
     * @param {string} value - The value to filter by, potentially multiple values separated by commas.
     */
    filterMultiple(key, value) {
        var oldData = this.postData.filter[key];
        if (oldData) {
            oldData = oldData.split(",");
            // Remove the value if it's already in the filter, else add it
            if (oldData.indexOf(value) >= 0) {
                oldData = $.grep(oldData, function (v) {
                    return v != value;
                });
            } else {
                oldData.push(value);
            }
            value = oldData.join(",");
        }
        this.filter(key, value); // Apply the updated filter
    }
    /**
     * Submits the filter form and updates the list accordingly.
     *
     * @param {HTMLFormElement} form - The form element containing the filter data.
     */
    filterFormSubmit(form) {
        this.postData.filter = $(form).serializeObject(); // Serialize the form data into an object
        this.loadList(1); // Reset to the first page after form submission
        app.setUrl(window.location.href, {
            page: 1,
            filter: JSON.stringify(this.postData.filter), // Update the URL with the serialized filter
        });
    }
}
/**
 * Handles image cropping functionality using the Cropper.js library.
 */
class ImageCrop {
    cropTarget = false; // HTML element for image cropping
    cropperObj = false; // Cropper.js instance
    uploadPath = ""; // Server upload URL
    cropperConfig = {
        aspectRatio: 1,
        cropBoxResizable: false,
        autoCropArea: 1,
    };
    /**
     * Initializes the ImageCrop instance.
     * @param {string} id - The ID of the HTML element for the cropper.
     * @param {string} uploadPath - The server upload URL.
     */
    init(id, uploadPath, followUp = {}) {
        this.cropTarget = document.getElementById(id);
        this.uploadPath = uploadPath;
        this.followUp = followUp;
    }
    /**
     * Sets the configuration for the cropper.
     * @param {Object} config - The configuration object for Cropper.js.
     */
    setConfig(config) {
        this.cropperConfig = config;
    }
    /**
     * Initializes the Cropper.js instance.
     */
    setCropper() {
        this.cropperObj = new Cropper(this.cropTarget, this.cropperConfig);
        $(".image-crop-action").show();
    }

    /**
     * Sets the cropper with a selected file.
     * @param {File} file - The file to be cropped.
     */
    setCropFile(file) {
        var validationResult = app.validateFile(file);
        if (!validationResult.status) {
            app.showMessage(
                "File is not valid! Please select only the following formats: [png, gif, jpeg, webp, jpg]",
                "error"
            );
            return false;
        }
        if (this.cropperObj) {
            this.cropperObj.destroy();
        }
        var _this = this;
        var reader = new FileReader();
        reader.onload = function (e) {
            _this.cropTarget.src = e.target.result;
            _this.setCropper();
        };
        reader.readAsDataURL(file);
    }
    /**
     * Uploads the cropped image to the server.
     * @returns {boolean} - Returns false if no image is selected.
     */
    uploadImage() {
        if (!this.cropperObj) {
            app.showMessage("Please select image", "error");
            return false;
        }
        app.showLoading();
        var _uploadPath = this.uploadPath;
        var _followUp = this.followUp;
        this.urltoFile(
            this.cropperObj.getCroppedCanvas().toDataURL(),
            "image.png",
            "image/png"
        ).then(function (file) {
            var formData = new FormData();
            formData.append(CSRF_NAME, CSRF_TOKEN);
            formData.append("image", file);
            $.ajax({
                url: _uploadPath,
                method: "post",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: (response) => app.ajaxSuccess(response, _followUp),
                error: app.ajaxError,
            });
        });
    }

    /**
     * Converts a data URL to a File object.
     * @param {string} url - The data URL to convert.
     * @param {string} filename - The name of the resulting file.
     * @param {string} mimeType - The MIME type of the file.
     * @returns {Promise<File>} - A promise resolving to a File object.
     */
    urltoFile(url, filename, mimeType) {
        return fetch(url)
            .then(function (res) {
                return res.arrayBuffer();
            })
            .then(function (buf) {
                return new File([buf], filename, { type: mimeType });
            });
    }
    /**
     * Retrieves the cropped image as a File object.
     * @returns {Promise<File>} - A promise resolving to the cropped file.
     */
    getFile() {
        return this.urltoFile(
            this.cropperObj.getCroppedCanvas().toDataURL(),
            "image.png",
            "image/png"
        );
    }
    /**
     * Rotates the image 90 degrees counterclockwise.
     */
    rotateLeft() {
        if (this.cropperObj) {
            this.cropperObj.rotate(90);
        }
    }
    /**
     * Rotates the image 90 degrees clockwise.
     */
    rotateRight() {
        if (this.cropperObj) {
            this.cropperObj.rotate(-90);
        }
    }
}

/**
 * Manages file uploads with drag-and-drop support.
 */
var fileDropBox = {
    files: [],
    fileDropBoxContainer: false,
    fileAccept: "image/png, image/jpeg, image/webp , image/jpg",
    fileInputHtml: "",
    /**
     * Initializes the file drop box.
     * @param {string} previewTarget - The selector for the preview container.
     * @param {Array<Object>} oldFiles - Previously uploaded files.
     */
    init: function (previewTarget, oldFiles) {
        (this.fileInputHtml =
            '<input style="visibility:hidden;" onchange="fileDropBox.selectFiles(this.files)" class="form-control file-input" type="file"  accept="' +
            this.fileAccept +
            '" multiple>'),
            (this.fileDropBoxContainer = $(previewTarget));
        var oldFilesHtml = "";
        $.each(oldFiles, function (index, fileData) {
            oldFilesHtml += fileDropBox.previewFile(
                fileData.type,
                fileData.url,
                fileData.name,
                fileData.name
            );
        });
        this.fileDropBoxContainer.html(
            '<div class="drop-file" ondragover="event.preventDefault();" ondrop="event.preventDefault();fileDropBox.selectFiles(event.dataTransfer.files);" onclick="$(this).next().find(\'.file-input\').click()"><h3 class="drop-text">Drop file here Or Click here to select File</h3></div><div class="file-input-container">' +
            fileDropBox.fileInputHtml +
            '</div><div class="file-preview row">' +
            oldFilesHtml +
            "</div>"
        );
    },
    /**
     * Handles file selection.
     * @param {FileList} files - The selected files.
     */
    selectFiles: function (files) {
        var previewHtml = "";
        $.each(files, function (index, file) {
            previewHtml += fileDropBox.previewFile(
                file.type,
                URL.createObjectURL(file),
                file.name,
                false
                
            );
            fileDropBox.files.push(file);
        });
        this.fileDropBoxContainer.find(".file-preview").append(previewHtml);
        this.fileDropBoxContainer
            .find(".file-input-container")
            .html(fileDropBox.fileInputHtml);
    },
    /**
     * Generates a preview for a file.
     * @param {string} type - The file type.
     * @param {string} url - The file URL.
     * @param {string} name - The file name.
     * @param {string|null} id - The file ID (for old files).
     * @returns {string} - The HTML string for the file preview.
     */
    previewFile: function (type, url, name, id) {
        var previewHtml = '<div class="file-preview-item mb-3 col-md-4">';
        if (type.match("video")) {
            previewHtml +=
                '<video class="video-preview" controls><source type="' +
                type +
                '" src="' +
                url +
                '"/></video>';
        } else if (type.match("image")) {
            previewHtml += '<img class="image-preview" src="' + url + '" />';
        } else {
            previewHtml += '<a href="' + url + '" class="pjax">' + name + "</a>";
        }
        previewHtml +=
            '<button onclick="fileDropBox.removeFile(this);" data-file-name="' +
            name +
            '" type="button" class="" style="display: flex;justify-content: center;align-items: end;"><i class="fa-solid fa-xmark"></i></button>';
        if (id) {
            previewHtml +=
                '<input type="hidden" name="file_old[]" value="' + id + '"></input>';
        }
        previewHtml += "</div>";
        return previewHtml;
    },
    /**
     * Removes a file from the preview and internal file list.
     * @param {HTMLElement} obj - The button element that triggered the removal.
     */
    removeFile: function (obj) {
        var target = $(obj);
        var filename = target.data("file-name");
        target.closest(".file-preview-item").remove();
        fileDropBox.files = $.grep(fileDropBox.files, function (file, index) {
            return file.name != filename;
        });
    },
    /**
     * Removes an old file from the preview.
     * @param {HTMLElement} obj - The button element that triggered the removal.
     */
    removeOldFile: function (obj) {
        $(obj).closest(".file-preview-item").remove();
    },
};

/**
 * Extends jQuery with a utility to serialize a form into a JSON object.
 */
(function ($) {
    $.fn.serializeObject = function () {
        var self = this,
            json = {},
            push_counters = {},
            patterns = {
                validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
                key: /[a-zA-Z0-9_]+|(?=\[\])/g,
                push: /^$/,
                fixed: /^\d+$/,
                named: /^[a-zA-Z0-9_]+$/,
            };

        /**
         * Builds a nested object structure.
         * @param {object|array} base - Base object or array to modify.
         * @param {string|number} key - Key or index.
         * @param {any} value - Value to assign.
         * @returns {object|array} Updated object or array.
         */
        this.build = function (base, key, value) {
            base[key] = value;
            return base;
        };
        /**
         * Generates a counter for push operations.
         * @param {string} key - Key to count pushes for.
         * @returns {number} Push counter.
         */
        this.push_counter = function (key) {
            if (push_counters[key] === undefined) {
                push_counters[key] = 0;
            }
            return push_counters[key]++;
        };

        $.each($(this).serializeArray(), function () {
            var k,
                keys = this.name.match(patterns.key),
                merge = this.value,
                reverse_key = this.name;
            while ((k = keys.pop()) !== undefined) {
                // Adjust reverse_key
                reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), "");
                if (k.match(patterns.push)) {
                    merge = self.build([], self.push_counter(reverse_key), merge);
                } else if (k.match(patterns.fixed)) {
                    merge = self.build([], k, merge);
                } else if (k.match(patterns.named)) {
                    merge = self.build({}, k, merge);
                }
            }
            json = $.extend(true, json, merge);
        });
        return json;
    };
})(jQuery);

/**custom functions */

function updateDataTableUrl(url) {
    datatableObj.settings().ajax.url(url).load();
}

function initEditorFull(editorElement, fileUploadUrl) {
    var seditor = editorElement.summernote({
        height: 200,
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["insert", ["link", "picture", "video"]],
            ["view", ["codeview"]],
        ],
        callbacks: {
            onImageUpload: function (files) {
                var formData = new FormData();
                formData.append("upload", files[0]);
                app.ajaxFilePost(fileUploadUrl, formData, function (response) {
                    if (response.status) {
                        seditor.summernote("insertImage", response.data.file_url);
                    } else {
                        app.showMessage(response.message, "error");
                    }
                });
            },
        },
    });
}

function initEditor(editorElement) {
    editorElement.summernote({
        height: 200,
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "underline", "clear"]],
            ["fontname", ["fontname"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["view", ["codeview"]], //'fullscreen','help'
        ],
    });
}

/**
 * Previews an image by updating the `src` attribute of the target element.
 *
 * @param {HTMLInputElement} input - The input element containing the image file.
 * @param {string} target - The selector for the target image element.
 */
function previewImage(input, target) {
    $(target).attr("src", URL.createObjectURL(input.files[0]));
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
}

function dataTableAjax(params) {
    return function (data, callback, settings) {
        data = { ...data, ...params.data };

        let cacheKey = params.url + JSON.stringify(data);
        let cachedData = AppCache.getData(cacheKey);
        if (cachedData) {
            callback(cachedData); // Load cached data
        }
        // Always fetch fresh data in the background
        data[CSRF_NAME] = CSRF_TOKEN;

        $.ajax({
            url: params.url,
            type: params.method,
            data: data,
            success: function (newData) {
                if (cachedData) {
                    delete newData.draw;
                    delete cachedData.draw;
                    //console.log(JSON.stringify(newData) === JSON.stringify(cachedData));
                    if (JSON.stringify(newData) === JSON.stringify(cachedData)) {
                        return false;
                    }
                }
                // Update cache
                AppCache.setData(cacheKey, newData);
                // Update DataTable only if cache was previously used
                callback(newData);
            },
            error: function () {
                console.error("Error fetching data.");
            },
        });
    };
}