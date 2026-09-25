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
     * @param {string} next - load | refresh | table_refresh | redirect | reload | hide_modal | show_modal_view
     * @param {string} [url] - Target for load, redirect and show_modal_view
     */
    runNextAction: function (next, url) {
        if (next === "load") {
            pjax.loadPage(url);
        } else if (next === "refresh") {
            pjax.loadPage(window.location.href);
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
        if (!app.isModalOpen(this.commonModel)) {
            app.openModal(this.commonModel);
        }
    },

    hideModal: function () {
        if (app.isModalOpen(this.commonModel)) {
            app.closeModal(this.commonModel);
        }
    },

    /**
     * Modals are `[data-modal]` elements (x-ui.modal): hidden by default, shown as a flex
     * overlay while `data-state="open"`. Open them with `[data-modal-open="#id"]`.
     */
    isModalOpen: function ($modal) {
        return $($modal).attr("data-state") === "open";
    },

    openModal: function ($modal) {
        $($modal).removeClass("hidden").addClass("flex").attr("data-state", "open");
        $("body").addClass("overflow-hidden");
    },

    closeModal: function ($modal) {
        $($modal).removeClass("flex").addClass("hidden").attr("data-state", "closed").trigger("modal:closed");
        if (!$("[data-modal][data-state=open]").length) {
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
        app.styleDataTables();
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
     * Replaces the palette classes of DataTables' Tailwind integration (gray-*, blue-*) with the
     * theme tokens, so tables follow light/dark. Runs once, before the first table is created.
     */
    styleDataTables: function () {
        if (app.dataTablesStyled) {
            return;
        }
        app.dataTablesStyled = true;
        const field =
            "border-input dark:bg-input/30 placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 h-9 rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-[3px]";
        $.extend(true, $.fn.dataTable.ext.classes, {
            search: { input: field + " ml-2" },
            length: { select: field + " mx-1 pr-8" },
            paging: {
                active: "bg-accent text-accent-foreground font-semibold",
                notActive: "bg-transparent",
                button: "relative inline-flex items-center justify-center border border-border -mr-px px-3 py-1.5 text-sm leading-6 hover:z-10 focus:z-10",
                first: "rounded-l-md",
                last: "rounded-r-md",
                enabled: "text-foreground hover:bg-accent",
                notEnabled: "text-muted-foreground opacity-50",
            },
            thead: {
                row: "border-b border-border",
                cell: "px-3 py-3 text-left font-medium text-foreground",
            },
            tbody: {
                row: "border-b border-border hover:bg-muted/50 transition-colors",
                cell: "p-3",
            },
            tfoot: {
                row: "border-t border-border",
                cell: "p-3 text-left",
            },
        });
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

    /** A reCAPTCHA token is single-use, so every form request needs a fresh one. */
    resetCaptcha: function () {
        try {
            if (window.grecaptcha && document.querySelector(".g-recaptcha")) {
                grecaptcha.reset();
            }
        } catch (e) {}
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
                this.resetCaptcha();
                cb(response);
            },
            error: (xhr, status, error) => {
                this.resetCaptcha();
                this.ajaxError(xhr, status, error);
            },
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
        $("#common-loader").removeClass("hidden").addClass("flex");
    },

    hideLoading: function () {
        // Swal.close();
        $("#common-loader").removeClass("flex").addClass("hidden");
    },

    showMessage: function (message, type) {
        const kinds = {
            success: { icon: "bx-check-circle", color: "text-success" },
            error: { icon: "bx-error-circle", color: "text-destructive" },
            warning: { icon: "bx-error", color: "text-amber-600 dark:text-amber-400" },
            info: { icon: "bx-info-circle", color: "text-muted-foreground" },
        };
        const kind = kinds[type] || kinds.info;
        const title = type.charAt(0).toUpperCase() + type.slice(1);
        const toastHtml = `
        <div class="bg-popover text-popover-foreground border-border pointer-events-auto flex w-80 max-w-[calc(100vw-2rem)] items-start gap-3 rounded-lg border p-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <i class="bx ${kind.icon} ${kind.color} text-xl"></i>
            <div class="flex-1 text-sm">
                <p class="font-medium">__title__</p>
                <p class="text-muted-foreground mt-0.5">__message__</p>
            </div>
            <button type="button" class="text-muted-foreground hover:text-foreground cursor-pointer" data-toast-dismiss aria-label="Close"><i class="bx bx-x text-lg"></i></button>
        </div>`;
        $("#common-toast").html(
            app.dataToHtml(toastHtml, { message: message, title: title })
        );
        setTimeout(function () {
            $("#common-toast").html("");
        }, 5000);
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
        return new Promise((resolve) => {
            const $modal = $("#confirm-modal");
            let answered = false;
            const answer = (value) => {
                if (answered) {
                    return;
                }
                answered = true;
                $modal.off(".confirm");
                resolve(value);
            };

            $("#confirm-modal-title").text(params.title || "Are you sure?");
            $("#confirm-modal-text").text(params.text || "");
            $("#confirm-modal-yes").text(params.confirmButtonText || "Yes");
            $("#confirm-modal-cancel").text(params.cancelButtonText || "No");

            $modal.on("click.confirm", "#confirm-modal-yes", function () {
                // Answer first: closing the modal also fires modal:closed, which means "no".
                answer(true);
                app.closeModal($modal);
            });
            // Cancel, the backdrop, the X and Escape all just close the modal.
            $modal.on("modal:closed.confirm", function () {
                answer(false);
            });
            app.openModal($modal);
            $("#confirm-modal-cancel").trigger("focus");
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

    // Error labels go under the field; a password field is wrapped with its show/hide button.
    if ($.validator) {
        $.validator.setDefaults({
            errorPlacement: function (error, element) {
                const $wrapper = element.closest("[data-slot=password-input]");
                error.insertAfter($wrapper.length ? $wrapper : element);
            },
        });
    }

    $(document).on("click", "[data-modal-open]", function (e) {
        e.preventDefault();
        app.openModal($($(this).data("modal-open")));
    });
    $(document).on("click", "[data-modal-dismiss]", function (e) {
        e.preventDefault();
        app.closeModal($(this).closest("[data-modal]"));
    });
    $(document).on("click", "[data-toast-dismiss]", function () {
        $(this).parent().remove();
    });
    $(document).on("click", "[data-alert-dismiss]", function () {
        $(this).closest("[data-slot=alert]").remove();
    });
    $(document).on("click", "[data-menu-toggle]", function (e) {
        e.preventDefault();
        $(this).closest("li").toggleClass("open");
    });
    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            $("[data-modal][data-state=open]").each(function () {
                app.closeModal($(this));
            });
        }
    });
});




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
            success: function (response) {
                // The endpoint answers with the {status, message, data} envelope; `data` is the DataTables payload.
                const newData = response.data;
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

/**
 * Small declarative UI behaviours (dropdowns, collapsible menus, the admin
 * sidebar, tabs, the theme switch). Everything is delegated from `document`,
 * so it keeps working on content that pjax.js swaps in. Markup opts in with
 * data attributes:
 *
 *   [data-dropdown] > [data-dropdown-toggle] + [data-dropdown-menu]   click toggles, outside click closes
 *   [data-collapse-toggle="#id"]     toggles `hidden` on #id and on the button's [data-toggle-icon]s
 *   [data-sidebar-toggle="open|close"]   admin sidebar (#layout-menu + #sidebar-backdrop)
 *   [data-tabs] > [data-tab="x"] + [data-tab-panel="x"]   data-tabs-active / data-tabs-inactive hold the classes
 *   [data-password-toggle="#input"]   show/hide a password field
 *   [data-app-sidebar] #app-sidebar + [data-app-sidebar-toggle]   user-area sidebar (layouts/user): icon rail on md+, sheet below
 *   [data-confirm-href]   asks for confirmation (data-confirm-title / -text / -yes), then navigates to the href
 *   [data-theme-switch]   colour theme + light/dark/system picker (x-ui.theme-switch, see app.ui.theme)
 */
app.ui = {
    init: function () {
        const $doc = $(document);

        $doc.on("click", "[data-dropdown-toggle]", function (event) {
            event.stopPropagation();
            const $dropdown = $(this).closest("[data-dropdown]");
            const $menu = $dropdown.find("[data-dropdown-menu]").first();
            $("[data-dropdown-menu]").not($menu).addClass("hidden");
            $menu.toggleClass("hidden");
        });

        // A click outside, or on a link/button inside a menu, closes the open dropdowns.
        $doc.on("click", function (event) {
            const $target = $(event.target);
            if ($target.closest("[data-dropdown-menu]").length && (!$target.closest("a, button").length || $target.closest("[data-keep-open]").length)) {
                return;
            }
            $("[data-dropdown-menu]").addClass("hidden");
        });

        $doc.on("click", "[data-collapse-toggle]", function (event) {
            event.stopPropagation();
            const $target = $($(this).data("collapseToggle"));
            app.ui.collapse($target, $target.hasClass("hidden"));
        });

        // A collapse with data-collapse-auto-close (the mobile navbar menu) closes on a link click, an outside
        // click and Escape, so it never stays open over the page it navigated to.
        $doc.on("click", "[data-collapse-auto-close] a", function () {
            app.ui.collapse($(this).closest("[data-collapse-auto-close]"), false);
        });
        $doc.on("click", function (event) {
            if (!$(event.target).closest("[data-collapse-auto-close], [data-collapse-toggle]").length) {
                app.ui.collapse($("[data-collapse-auto-close]"), false);
            }
        });
        // Growing past the desktop breakpoint swaps in the desktop navigation; drop the mobile state.
        window.matchMedia("(min-width: 1024px)").addEventListener("change", function (mq) {
            if (mq.matches) {
                app.ui.collapse($("[data-collapse-auto-close]"), false);
                app.ui.sidebar(false);
            }
        });

        $doc.on("click", "[data-sidebar-toggle]", function () {
            app.ui.sidebar($(this).data("sidebarToggle") === "open");
        });
        $doc.on("keydown", function (event) {
            if (event.key === "Escape") {
                $("[data-dropdown-menu]").addClass("hidden");
                app.ui.collapse($("[data-collapse-auto-close]"), false);
                app.ui.sidebar(false);
            }
        });
        // Following a link inside the sidebar (pjax) should reveal the page on small screens.
        $doc.on("click", "#layout-menu a.pjax", function () {
            app.ui.sidebar(false);
        });

        $doc.on("click", "[data-tab]", function () {
            const $button = $(this);
            const $tabs = $button.closest("[data-tabs]");
            const active = String($tabs.data("tabsActive") || "");
            const inactive = String($tabs.data("tabsInactive") || "");
            const name = $button.data("tab");

            $tabs.find("[data-tab]").each(function () {
                const isActive = $(this).data("tab") === name;
                $(this)
                    .removeClass(isActive ? inactive : active)
                    .addClass(isActive ? active : inactive)
                    .attr("aria-selected", isActive);
            });
            $tabs.find("[data-tab-panel]").each(function () {
                $(this).toggleClass("hidden", $(this).data("tabPanel") !== name);
            });
        });

        // Show/hide toggle of x-ui.password-input.
        $doc.on("click", "[data-password-toggle]", function () {
            const $input = $($(this).data("passwordToggle"));
            const reveal = $input.attr("type") === "password";
            $input.attr("type", reveal ? "text" : "password");
            $(this).find("i").toggleClass("bx-hide", !reveal).toggleClass("bx-show", reveal);
        });

        app.ui.appSidebar.init();

        $doc.on("click", "[data-confirm-href]", function (event) {
            event.preventDefault();
            const $button = $(this);
            app.showConfirmationPopup({
                title: $button.data("confirmTitle") || "Are you sure?",
                text: $button.data("confirmText") || "",
                confirmButtonText: $button.data("confirmYes") || "Yes",
            }).then(function (confirmed) {
                if (confirmed) {
                    window.location.href = $button.data("confirmHref");
                }
            });
        });

        app.ui.theme.init();
    },

    /**
     * The user-area sidebar (Next's shadcn Sidebar, inset variant). From md up the toggle collapses it to an icon rail
     * (data-state on #user-shell, remembered in the `sidebar_state` cookie the layout reads); below md it is an
     * off-canvas sheet over a backdrop. Ctrl/Cmd+B toggles it, like shadcn.
     */
    appSidebar: {
        cookie: "sidebar_state",

        isMobile: function () {
            return window.matchMedia("(max-width: 767px)").matches;
        },

        isMobileOpen: function () {
            return $("#app-sidebar").hasClass("!translate-x-0");
        },

        toggle: function () {
            if (this.isMobile()) {
                this.setMobile(!this.isMobileOpen());
            } else {
                this.setOpen($("[data-app-sidebar]").attr("data-state") === "collapsed");
            }
        },

        setOpen: function (open) {
            $("[data-app-sidebar]").attr("data-state", open ? "expanded" : "collapsed");
            $("[data-app-sidebar-toggle]").attr("aria-expanded", open);
            document.cookie = this.cookie + "=" + open + "; path=/; max-age=604800; SameSite=Lax";
        },

        setMobile: function (open) {
            $("#app-sidebar").toggleClass("!translate-x-0", open);
            $("[data-app-sidebar-backdrop]").toggleClass("hidden", !open);
            $("[data-app-sidebar-toggle]").attr("aria-expanded", open);
            if (open) {
                $("body").addClass("overflow-hidden");
            } else if (!$("[data-modal][data-state=open]").length) {
                $("body").removeClass("overflow-hidden");
            }
        },

        init: function () {
            const sidebar = this;
            const $doc = $(document);

            $doc.on("click", "[data-app-sidebar-toggle]", function () {
                sidebar.toggle();
            });
            $doc.on("click", "[data-app-sidebar-backdrop]", function () {
                sidebar.setMobile(false);
            });
            // Following a link (pjax) should reveal the page on phones.
            $doc.on("click", "#app-sidebar a", function () {
                if (sidebar.isMobile()) {
                    sidebar.setMobile(false);
                }
            });
            $doc.on("keydown", function (event) {
                if (event.key === "Escape" && sidebar.isMobileOpen()) {
                    sidebar.setMobile(false);
                }
                if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "b" && $("[data-app-sidebar]").length) {
                    event.preventDefault();
                    sidebar.toggle();
                }
            });
            window.matchMedia("(min-width: 768px)").addEventListener("change", function (query) {
                if (query.matches) {
                    sidebar.setMobile(false);
                }
            });
        },
    },

    /** Shows or hides a collapsible element and syncs its toggle buttons (aria-expanded, menu/close icon). */
    collapse: function ($targets, open) {
        $targets.each(function () {
            const $target = $(this);
            $target.toggleClass("hidden", !open);
            $("[data-collapse-toggle='#" + this.id + "']").each(function () {
                const $icons = $(this).find("[data-toggle-icon]");
                $(this).attr("aria-expanded", open);
                // First icon = closed state, second = open state.
                $icons.eq(0).toggleClass("hidden", open);
                $icons.eq(1).toggleClass("hidden", !open);
            });
        });
    },

    /** Admin sidebar drawer (below lg): slides in over a backdrop and locks page scroll while open. */
    sidebar: function (open) {
        const $menu = $("#layout-menu");
        if (!$menu.length) return;
        $menu.toggleClass("!translate-x-0", open);
        $("#sidebar-backdrop").toggleClass("hidden", !open);
        $("[data-sidebar-toggle=open]").attr("aria-expanded", open);
        if (open) {
            $("body").addClass("overflow-hidden");
        } else if (!$("[data-modal][data-state=open]").length) {
            $("body").removeClass("overflow-hidden");
        }
    },

    /**
     * Colour theme + light/dark/system mode (Next's "switchcn").
     *
     * Mode: `app-color-mode` (localStorage and cookie) is light | dark | system; the resolved value is the `dark`
     * class plus data-theme on <html>, which next-theme.css and DataTables key off. `system` follows
     * prefers-color-scheme live.
     *
     * Theme: `app-theme` (localStorage and cookie, so the server can render it without a flash) names one of
     * the themes listed by GET /theme. Its CSS variables come from GET /theme/{name} and live in
     * <style id="app-theme-vars"> (the `default` theme is the empty override). common/theme-init.blade.php renders the
     * same style tag and the mode before first paint.
     */
    theme: {
        modeKey: "app-color-mode",
        themeKey: "app-theme",
        modes: ["light", "system", "dark"],
        themes: null,
        cache: {},

        read: function (key) {
            try {
                return localStorage.getItem(key);
            } catch (e) {
                return null;
            }
        },

        write: function (key, value) {
            try {
                localStorage.setItem(key, value);
            } catch (e) {}
            // The server reads these to render the theme; one year, plain cookies.
            document.cookie = key + "=" + encodeURIComponent(value) + "; path=/; max-age=31536000; SameSite=Lax";
        },

        mode: function () {
            const mode = this.read(this.modeKey);
            return this.modes.indexOf(mode) >= 0 ? mode : "system";
        },

        /** The theme the page was rendered with, or the last one this browser picked. */
        rendered: function () {
            return String($("#app-theme-vars").attr("data-theme-name") || "default");
        },

        prefersDark: function () {
            return window.matchMedia("(prefers-color-scheme: dark)").matches;
        },

        /** Applies a mode to the document and to every switch on the page. */
        applyMode: function (mode) {
            const dark = mode === "dark" || (mode === "system" && this.prefersDark());
            const root = document.documentElement;
            root.classList.toggle("dark", dark);
            root.classList.toggle("cc--darkmode", dark);
            root.setAttribute("data-theme", dark ? "dark" : "light");

            $("[data-theme-mode-icon]").each(function () {
                $(this).toggleClass("hidden", $(this).data("themeModeIcon") !== mode);
            });
            $("[data-theme-cycle]").attr("title", "Mode: " + mode + " — click to cycle");
        },

        setMode: function (mode) {
            if (this.modes.indexOf(mode) < 0) {
                return;
            }
            this.write(this.modeKey, mode);
            this.applyMode(mode);
        },

        cycleMode: function () {
            this.setMode(this.modes[(this.modes.indexOf(this.mode()) + 1) % this.modes.length]);
        },

        /** Injects a loaded theme (`{name, css, font_href}`) into the page. */
        inject: function (theme) {
            let $style = $("#app-theme-vars");
            if (!$style.length) {
                $style = $("<style>", { id: "app-theme-vars" }).appendTo("head");
            }
            $style.text(theme.css || "").attr("data-theme-name", theme.name);

            let $fonts = $("#app-theme-fonts");
            if (theme.font_href) {
                if (!$fonts.length) {
                    $fonts = $("<link>", { id: "app-theme-fonts", rel: "stylesheet" }).appendTo("head");
                }
                $fonts.attr("href", theme.font_href);
            } else {
                $fonts.remove();
            }
            this.markActive(theme.name);
        },

        /** Marks the active row in every open list and puts its name on each trigger. */
        markActive: function (name) {
            const entry = (this.themes || []).find((item) => item.name === name);
            $("[data-theme-list] [data-theme-name]").each(function () {
                $(this).attr("aria-checked", $(this).data("themeName") === name);
            });
            if (entry) {
                $("[data-theme-trigger]").attr("title", "Active Theme: " + entry.label);
            }
        },

        /** Picks a theme: remembered, sent to the server as a cookie, and applied without a reload. */
        setTheme: function (name) {
            const theme = this;
            const switchUrl = $("[data-theme-switch]").first().data("themeUrl");
            if (!switchUrl || !/^[a-z0-9-]+$/.test(name)) {
                return;
            }

            theme.write(theme.themeKey, name);

            if (name === "default") {
                theme.inject({ name: name, css: "", font_href: null });
                return;
            }
            if (theme.cache[name]) {
                theme.inject(theme.cache[name]);
                return;
            }
            $.ajax({
                url: switchUrl.replace("__name__", name),
                method: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.status == 1) {
                        theme.cache[name] = response.data;
                        // A quicker second click may have chosen another theme meanwhile.
                        if (theme.read(theme.themeKey) === name) {
                            theme.inject(response.data);
                        }
                    }
                },
            });
        },

        /** Lists the themes once; each open switch clones the row template per theme. */
        withThemes: function (cb) {
            const theme = this;
            if (theme.themes) {
                cb(theme.themes);
                return;
            }
            $.ajax({
                url: $("[data-theme-switch]").first().data("themesUrl"),
                method: "GET",
                dataType: "json",
                success: function (response) {
                    if (response.status == 1) {
                        theme.themes = response.data.themes;
                        cb(theme.themes);
                    }
                },
            });
        },

        /** Fills one switch's list (first open) and applies the search filter. */
        render: function ($switch) {
            const theme = this;
            const $list = $switch.find("[data-theme-list]");
            if (!$list.data("rendered")) {
                const template = $switch.find("[data-theme-row-template]")[0];
                theme.themes.forEach(function (entry) {
                    const $row = $(template.content.cloneNode(true)).children().first();
                    $row.attr("data-theme-name", entry.name).data("themeName", entry.name);
                    $row.find("[data-theme-label]").text(entry.label);
                    entry.swatches.forEach(function (color) {
                        $("<span>", { class: "border-border inline-block size-[13px] shrink-0 rounded-full border" })
                            .css("background", color)
                            .appendTo($row.find("[data-theme-swatches]"));
                    });
                    $row.attr("data-theme-key", entry.label.toLowerCase());
                    $list.append($row);
                });
                $list.data("rendered", true);
            }
            theme.filter($switch);
            theme.markActive(String(theme.read(theme.themeKey) || theme.rendered()));
        },

        filter: function ($switch) {
            const term = String($switch.find("[data-theme-search]").val() || "").toLowerCase();
            let shown = 0;
            $switch.find("[data-theme-list] > [data-theme-name]").each(function () {
                const match = String($(this).attr("data-theme-key")).indexOf(term) >= 0;
                $(this).toggleClass("hidden", !match);
                shown += match ? 1 : 0;
            });
            $switch.find("[data-theme-empty]").toggleClass("hidden", shown > 0);
            $switch.find("[data-theme-count]").text(shown + " theme" + (shown === 1 ? "" : "s"));
        },

        init: function () {
            const theme = this;
            const $doc = $(document);

            theme.applyMode(theme.mode());

            // The visitor picked a theme that the server did not render (cookie cleared, or picked in another
            // tab): apply the remembered one.
            const remembered = theme.read(theme.themeKey);
            if (remembered && remembered !== theme.rendered() && /^[a-z0-9-]+$/.test(remembered)) {
                theme.setTheme(remembered);
            }

            $doc.on("click", "[data-theme-trigger]", function () {
                const $switch = $(this).closest("[data-theme-switch]");
                theme.withThemes(function () {
                    theme.render($switch);
                    setTimeout(function () {
                        $switch.find("[data-theme-search]").trigger("focus");
                    }, 50);
                });
            });
            $doc.on("input", "[data-theme-search]", function () {
                theme.filter($(this).closest("[data-theme-switch]"));
            });
            $doc.on("click", "[data-theme-list] [data-theme-name]", function () {
                theme.setTheme(String($(this).data("themeName")));
                $(this).closest("[data-theme-switch]").find("[data-theme-search]").val("");
            });
            $doc.on("click", "[data-theme-cycle]", function () {
                theme.cycleMode();
            });
            $doc.on("click", "[data-theme-shuffle]", function () {
                theme.withThemes(function (themes) {
                    theme.setTheme(themes[Math.floor(Math.random() * themes.length)].name);
                });
            });

            const query = window.matchMedia("(prefers-color-scheme: dark)");
            const onSystemChange = function () {
                if (theme.mode() === "system") {
                    theme.applyMode("system");
                }
            };
            if (query.addEventListener) {
                query.addEventListener("change", onSystemChange);
            } else {
                query.addListener(onSystemChange);
            }
            // Another tab changed the choice.
            window.addEventListener("storage", function (event) {
                if (event.key === theme.modeKey) {
                    theme.applyMode(theme.mode());
                } else if (event.key === theme.themeKey && event.newValue) {
                    theme.setTheme(event.newValue);
                }
            });
        },
    },
};

/**
 * Ajax grid pagination (x-ui.pagination + a [data-ajax-grid] container). The buttons are real links, so the
 * page works without JS; here a click on a page link / the Rows select, or a submit of a
 * form[data-ajax-grid-form="#grid"], fetches the target URL, swaps the grid's contents and updates the address bar.
 *
 *   <form data-ajax-grid-form="#blog-grid" method="get">…</form>
 *   <div id="blog-grid" data-ajax-grid> …items… <x-ui.pagination …/> </div>
 */
app.pagination = {
    init: function () {
        const $doc = $(document);

        $doc.on("click", "[data-ajax-grid] a[data-page-link]", function (e) {
            if (e.ctrlKey || e.metaKey || e.shiftKey) return;
            e.preventDefault();
            app.pagination.load($(this).closest("[data-ajax-grid]"), this.href);
        });

        $doc.on("change", "[data-ajax-grid] select[data-pagination-limit]", function () {
            app.pagination.load($(this).closest("[data-ajax-grid]"), this.value);
        });

        $doc.on("submit", "form[data-ajax-grid-form]", function (e) {
            const $grid = $($(this).data("ajaxGridForm"));
            if (!$grid.length) return;
            e.preventDefault();
            const query = $(this).serialize();
            app.pagination.load($grid, this.action.split("?")[0] + (query ? "?" + query : ""));
        });
    },

    load: function ($grid, url) {
        const gridId = $grid.attr("id");
        const layout = $("#main-container").data("layout");
        const fetchUrl = url + (url.includes("?") ? "&" : "?") + "partial=1&layout=" + layout;

        $grid.addClass("pointer-events-none opacity-50 transition-opacity");
        $.ajax({ url: fetchUrl, method: "GET" })
            .done(function (response) {
                const $fresh = $("<div>").append($.parseHTML(String(response))).find("#" + gridId);
                if (!$fresh.length) {
                    window.location.href = url;
                    return;
                }
                $grid.html($fresh.html());
                window.history.pushState({}, "", url);
                if ($grid[0].getBoundingClientRect().top < 0) {
                    $grid[0].scrollIntoView({ behavior: "smooth", block: "start" });
                }
            })
            .fail(function () {
                window.location.href = url;
            })
            .always(function () {
                $grid.removeClass("pointer-events-none opacity-50 transition-opacity");
            });
    },
};

$(function () {
    app.ui.init();
    app.pagination.init();
});
