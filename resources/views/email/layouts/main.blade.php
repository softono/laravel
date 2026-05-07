<?php
$primaryColor = 'green';
?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="format-detection" content="date=no" />
    <meta name="format-detection" content="address=no" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="x-apple-disable-message-reformatting" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <title>{{ $subject }}</title>
    <style type="text/css" media="screen">
        /* Linked Styles */
        * {
            font-family: 'Roboto', sans-serif;
        }

        body {
            padding: 0 !important;
            margin: 0 !important;
            display: block !important;
            min-width: 100% !important;
            width: 100% !important;
            background: #d9d9d9;
            -webkit-text-size-adjust: none
        }

        a {
            color: #000001;
            text-decoration: none
        }

        p {
            padding: 0 !important;
            margin: 0 !important
        }

        img {
            -ms-interpolation-mode: bicubic;
            /* Allow smoother rendering of resized image in Internet Explorer */
        }

        .mcnPreviewText {
            display: none !important;
        }

        .text-footer2 a {
            color: #ffffff;
        }

        .m0 {
            margin: 0px;
        }

        .bg-primary {
            background: #85b33a;
        }

        td.description {
            padding-bottom: 0 !important;
        }

        .im {
            color: #666666 !important;
        }

        .ii a[href],
        a {
            color: #666666 !important;
        }

        /* Mobile styles */
        @media only screen and (max-device-width: 480px),
        only screen and (max-width: 480px) {
            .mobile-shell {
                width: 100% !important;
                min-width: 100% !important;
            }

            .text-h2,
            .text-size-h2 {
                font-size: 24px !important;
            }

            .m-center {
                text-align: center !important;
            }

            .m-left {
                text-align: left !important;
                margin-right: auto !important;
            }

            .center {
                margin: 0 auto !important;
            }

            .content2 {
                padding: 8px 15px 12px !important;
            }

            .t-left {
                float: left !important;
                margin-right: 30px !important;
            }

            .t-left-2 {
                float: left !important;
            }

            .td {
                width: 100% !important;
                min-width: 100% !important;
            }

            .content {
                padding: 30px 15px !important;
            }

            .section {
                padding: 30px 15px 0px !important;
            }

            .m-br-15 {
                height: 15px !important;
            }

            .mpb5 {
                padding-bottom: 5px !important;
            }

            .mpb15 {
                padding-bottom: 15px !important;
            }

            .mpb20 {
                padding-bottom: 20px !important;
            }

            .mpb30 {
                padding-bottom: 30px !important;
            }

            .m-padder {
                padding: 0px 15px !important;
            }

            .m-padder2 {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }

            .p70 {
                padding: 30px 0px !important;
            }

            .pt70 {
                padding-top: 30px !important;
            }

            .p0-15 {
                padding: 0px 15px !important;
            }

            .p30-15 {
                padding: 30px 15px !important;
            }

            .p30-15-0 {
                padding: 30px 15px 0px 15px !important;
            }

            .p0-15-30 {
                padding: 0px 15px 30px 15px !important;
            }


            .text-footer {
                text-align: center !important;
            }

            .m-td,
            .m-hide {
                display: none !important;
                width: 0 !important;
                height: 0 !important;
                font-size: 0 !important;
                line-height: 0 !important;
                min-height: 0 !important;
            }

            .m-block {
                display: block !important;
            }

            .fluid-img img {
                width: 100% !important;
                max-width: 100% !important;
                height: auto !important;
            }

            .column,
            .column-dir,
            .column-top,
            .column-empty,
            .column-top-30,
            .column-top-60,
            .column-empty2,
            .column-bottom {
                float: left !important;
                width: 100% !important;
                display: block !important;
            }

            .column-empty {
                padding-bottom: 15px !important;
            }

            .column-empty2 {
                padding-bottom: 30px !important;
            }

            .content-spacing {
                width: 15px !important;
            }
        }
    </style>
</head>

<body class="body"
    style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; background:#d9d9d9; -webkit-text-size-adjust:none;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" valign="top">
                <!-- Main -->
                <table style="width:100%; max-width:650px; padding: 14px;" border="0" cellspacing="0"
                    cellpadding="0" class="mobile-shell">
                    <tr>
                        <td class="td"
                            style="width:100%; max-width:650px; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal;">
                            <!-- Header -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="p30-15" style="padding: 40px 0px 20px 0px;">
                                    </td>
                                </tr>
                                <!-- END Top bar -->
                                <!-- Logo -->
                                <tr>
                                    <td bgcolor="#ffffff" class="p30-15 img-center"
                                        style="padding:16px; border-radius: 20px 20px 0px 0px; text-align:center;">
                                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                            height="50" border="0" alt="" />
                                    </td>
                                </tr>
                                <!-- END Logo -->
                                <!-- Nav -->
                                <tr>
                                    <td class="text-nav-white bg-primary"
                                        style="color:#85b33a;font-size:12px; line-height:22px; text-align:center; text-transform:uppercase; padding:2px 0px;">
                                    </td>
                                </tr>
                                <!-- END Nav -->
                            </table>
                            <!-- END Header -->
                            @yield('content')
                            <!-- Footer -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="p30-15-0" bgcolor="#ffffff" style="border-radius: 0px 0px 20px 20px;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td align="center" class="p30-15"
                                                    style="border-top: 1px solid #ebebeb; padding: 30px;">
                                                    <table class="center" border="0" cellspacing="0" cellpadding="0"
                                                        style="text-align:center;">
                                                        <tr>
                                                            <td class="text-center"
                                                                style="color:#5d5c5c;font-size:14px; line-height:22px; text-align:center; ">
                                                                &copy; {{ config('app.APP_NAME') }} {{ date('Y') }}.
                                                                All rights reserved.</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- END Footer -->
                        </td>
                    </tr>
                    <tr>
                        <td class="p30-15" style="padding: 40px 0px 20px 0px;">
                        </td>
                    </tr>
                </table>
                <!-- END Main -->
            </td>
        </tr>
    </table>
</body>

</html>
