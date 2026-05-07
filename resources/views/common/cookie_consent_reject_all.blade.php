 @if(config('setting.cookie_consent') && !isset($_COOKIE['cookie_consent']))
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.css">
    <script type="module">
        import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js';

        window.addEventListener('load', function() {
            if (window.templateCustomizer?.settings?.style === 'dark') {
                document.documentElement.classList.add('cc--darkmode');
            }
            
            setTimeout(function(){
                if(CookieConsent.getUserPreferences().rejectedCategories.indexOf('necessary')>=0){
                    console.log('revision');
                    CookieConsent.show(true);
                }
            }, 800);
            
            CookieConsent.run({
                
                // root: 'body',
                // autoShow: true,
                //disablePageInteraction: true,
                // hideFromBots: true,
                // mode: 'opt-in',
                revision: 100,

                cookie: {
                    name: 'cc_cookie',
                    // domain: location.hostname,
                    // path: '/',
                    // sameSite: "Lax",
                    expiresAfterDays: 365,
                },

                // https://cookieconsent.orestbida.com/reference/configuration-reference.html#guioptions
                guiOptions: {
                    consentModal: {
                        layout: 'cloud inline',
                        position: 'bottom right',
                        equalWeightButtons: true,
                        flipButtons: false
                    },
                    preferencesModal: {
                        layout: 'box',
                        equalWeightButtons: true,
                        flipButtons: false
                    }
                },

                onFirstConsent: ({cookie}) => {
                    console.log('onFirstConsent fired',cookie);
                },

                onConsent: ({cookie}) => {
                    console.log('onConsent fired!', cookie);
                    
                },

                onChange: ({changedCategories, changedServices}) => {
                    console.log('onChange fired!', changedCategories, changedServices);
                    if(CookieConsent.getUserPreferences().rejectedCategories.indexOf('necessary')<0){
                        app.setCookie('cookie_consent',1);
                    }
                },

                onModalReady: ({modalName}) => {
                    console.log('ready:', modalName);
                },

                onModalShow: ({modalName}) => {
                    console.log('visible:', modalName);
                },

                onModalHide: ({modalName}) => {
                    console.log('hidden:', modalName);
                    setTimeout(function(){
                        if(CookieConsent.getUserPreferences().rejectedCategories.indexOf('necessary')>=0){
                            console.log('revision');
                            window.location.reload();
                        }
                    }, 800);
                },

                categories: {
                    necessary: {
                        enabled: false,  // this category is enabled by default
                        readOnly: false  // this category cannot be disabled
                    },
                    analytics: {
                        autoClear: {
                            cookies: [
                                {
                                    name: /^_ga/,   // regex: match all cookies starting with '_ga'
                                },
                                {
                                    name: '_gid',   // string: exact cookie name
                                }
                            ]
                        },

                        // https://cookieconsent.orestbida.com/reference/configuration-reference.html#category-services
                        services: {
                            ga: {
                                label: 'Google Analytics',
                                onAccept: () => {},
                                onReject: () => {}
                            },
                            youtube: {
                                label: 'Youtube Embed',
                                onAccept: () => {},
                                onReject: () => {}
                            },
                        }
                    },
                    //ads: {}
                },

                language: {
                    default: 'en',
                    translations: {
                        en: {
                            consentModal: {
                                title: 'We use cookies',
                                description: 'We use cookies to provide our services and for analytics and marketing. To find out more about our use of cookies, please see our Privacy Policy. By continuing to browse our website, you agree to our use of cookies. <a href="page/cookie-policy">Cookie policy</a>',
                                acceptAllBtn: 'Accept all',
                                acceptNecessaryBtn: 'Reject all',
                                showPreferencesBtn: 'Manage Individual preferences',
                                // closeIconLabel: 'Reject all and close modal',
                                footer: ``,
                            },
                            preferencesModal: {
                                title: 'Manage cookie preferences',
                                acceptAllBtn: 'Accept all',
                                acceptNecessaryBtn: 'Reject all',
                                savePreferencesBtn: 'Accept current selection',
                                closeIconLabel: 'Close modal',
                                serviceCounterLabel: 'Service|Services',
                                sections: [
                                    {
                                        title: 'Your Privacy Choices',
                                        description: `In this panel you can express some preferences related to the processing of your personal information. You may review and change expressed choices at any time by resurfacing this panel via the provided link. To deny your consent to the specific processing activities described below, switch the toggles to off or use the “Reject all” button and confirm you want to save your choices.`,
                                    },
                                    {
                                        title: 'Strictly Necessary',
                                        description: 'These cookies are essential for the proper functioning of the website and cannot be disabled.',

                                        //this field will generate a toggle linked to the 'necessary' category
                                        linkedCategory: 'necessary',
                                        cookieTable: {
                                            caption: 'Cookie table',
                                            headers: {
                                                name: 'Cookie',
                                                domain: 'Domain',
                                                desc: 'Description'
                                            },
                                            body: [
                                                {
                                                    name: 'XSRF-TOKEN',
                                                    domain: location.hostname,
                                                    desc: 'csrf security',
                                                },
                                                {
                                                    name: APP_UID+'_session',
                                                    domain: location.hostname,
                                                    desc: 'user session',
                                                },
                                                {
                                                    name: APP_UID+'_token',
                                                    domain: location.hostname,
                                                    desc: 'user remember',
                                                },
                                                {
                                                    name: APP_UID+'_tz',
                                                    domain: location.hostname,
                                                    desc: 'timezone',
                                                }
                                            ]
                                        }
                                    },
                                    {
                                        title: 'Performance and Analytics',
                                        description: 'These cookies collect information about how you use our website. All of the data is anonymized and cannot be used to identify you.',
                                        linkedCategory: 'analytics',
                                        cookieTable: {
                                            caption: 'Cookie table',
                                            headers: {
                                                name: 'Cookie',
                                                domain: 'Domain',
                                                desc: 'Description'
                                            },
                                            body: [
                                                {
                                                    name: '_ga',
                                                    domain: location.hostname,
                                                    desc: 'Description 1',
                                                },
                                                {
                                                    name: '_gid',
                                                    domain: location.hostname,
                                                    desc: 'Description 2',
                                                }
                                            ]
                                        }
                                    },
                                    {
                                        title: 'More information',
                                        description: 'For any queries in relation to my policy on cookies and your choices, please <a href="contact">contact us</a>'
                                    }
                                ]
                            }
                        }
                    }
                }
            });
        });
    </script>
@endif
