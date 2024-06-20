<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>

<style>
    @media only screen and (max-width: 600px) {
        .inner-body {
            width: 100% !important;
        }

        .footer {
            width: 100% !important;
        }

        .mail-col {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .data-col {
            width: 100% !important;
        }

        .data-col-1 {
            width: 100% !important;
        }

        .data-col-2 {
            width: 100% !important;
            position: relative !important;
            margin-top: 20px;
        }

        .data-table {
            min-height: 0 !important;
        }
    }

    @media only screen and (max-width: 500px) {
        .button {
            width: 100% !important;
        }

        .mail-col {
            width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .data-col {
            width: 100% !important;
        }

        .data-col-1 {
            width: 100% !important;
        }

        .data-col-2 {
            width: 100% !important;
            position: relative !important;
            margin-top: 20px;
        }

        .data-table {
            min-height: 0 !important;
        }

        .mail-col-b {
            width: 100% !important;
        }
    }

    @media only screen and (max-width: 1000px) and (min-width: 601px) {
        .mail-col {
            width: 40% !important;
        }
    }


    td {
        border-bottom: none !important;
        border-top: none !important;
    }

    .inner-body {
        background-color: #FFFFFF;
        margin: 0 auto;
        padding: 0;
        width: 100% !important;
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        -premailer-width: 100% !important;
        /*
        border: 1px solid #cccccc;
        */
        border-radius: 10px;
    }
/*
    .wrapper {
        padding: 20px 45px !important;
    }
*/
    .mail-row {
        display: block;
        position: relative;
        text-align: center;
    }

    .mail-col {
        display: inline-block;
        position: relative;
        margin: 8px 10px;
        border-radius: 4px;
        padding: 25px;
        color: #ffffff;
        width: 38%;
    }

    .mail-col-b {
        display: inline-block;
        position: relative;
        margin: 8px 10px;
        border-radius: 4px;
        padding: 25px;
        color: #ffffff;
        width: 30%;
    }

    .mail-col * {
        color: #FFFFFF;
    }

    .mail-col-b * {
        color: #FFFFFF;
    }

    .mail-det-row {
        display: block;
        position: relative;
        text-align: left
    }

    .mail-det-col {
        display: inline-block;
        text-align: left;
    }

    .mail-col-b .mail-det-col{
        display: block;
        text-align: center !important;
    }

    .mail-det-col h2 {
        font-size: 42px;
        font-weight: normal;
        margin: 0;
        padding-left: 25px;
        padding-right: 25px;
    }

    .mail-det-col p {
        position: relative;
        box-sizing: content-box !important;
    }

    .data-row {
        display: block;
        position: relative;
        margin-top: 30px;
    }

    .data-col {
        display: inline-block;
        position: relative;
    }

    .data-col-1 {
        width: 62%;
    }

    .data-col-2 {
        width: 37%;
    }

    .data-table {
        width: 100%;
        border-spacing: 0 !important;
        min-height: 300px;
    }

    .data-table thead th {
        background: #dddddd;
        text-transform: uppercase;
        font-size: 13px;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .data-table tbody td {
        font-size: 13px;
        padding-top: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #999999 !important;
    }

</style>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table style="border: none" class="content" width="100%" cellpadding="0" cellspacing="0">
            {{ $header ?? '' }}

            <!-- Email Body -->
                <tr>
                    <td class="body" style="background-color: inherit" width="100%" cellpadding="0" cellspacing="0">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell">
                                    {{ Illuminate\Mail\Markdown::parse($slot) }}

                                    {{ $subcopy ?? '' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{ $footer ?? '' }}
            </table>
        </td>
    </tr>
</table>

    {{--<style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>--}}

    {{--<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>--}}
</body>
</html>
