<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ config('app.name') }}</title>
<link href="https://api.fontshare.com/v2/css?f[]=satoshi@1&display=swap" rel="stylesheet">
<style>
/* Basic Resets */
body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }

/* Markdown Conversions */
.content-padding h1, .content-padding h2, .content-padding h3 { color: #525f7f; margin-top: 0; }
.content-padding p { font-size: 15px; line-height: 24px; color: #525f7f; margin: 0 0 16px 0; }
.content-padding ul, .content-padding ol { padding-left: 20px; margin: 0 0 20px 0; color: #333333; font-size: 15px; line-height: 24px; }
.content-padding li { margin-bottom: 8px; }
.content-padding a { color: #FF641A; text-decoration: none; }

/* Subcopy Styling */
.subcopy { border-top: 1px solid #e8e5ef; margin-top: 25px; padding-top: 25px; }
.subcopy p { font-size: 14px; color: #b0adc5; line-height: 1.5em; margin-bottom: 0; }

/* Mobile Responsiveness */
@media screen and (max-width: 600px) {
.container { width: 100% !important; max-width: 100% !important; }
.content-padding { padding: 30px 20px !important; }
}
</style>
{!! $head ?? '' !!}
</head>
<body style="margin: 0; padding: 0; background-color: #f6f9fc; font-family: 'Satoshi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">

<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f6f9fc;">
<tr>
<td align="center" style="padding: 40px 10px;">

<table class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; margin: 0 auto; border-bottom: 5px solid #73BC1C; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">

{!! $header ?? '' !!}

<tr>
<td class="content-padding" style="padding: 40px 48px;">

{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}

</td>
</tr>

</table>

<table class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="margin: 0 auto;">
{!! $footer ?? '' !!}
</table>

</td>
</tr>
</table>

</body>
</html>
