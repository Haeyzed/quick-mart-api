<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
    <meta name="x-apple-disable-message-reformatting" />
    <link
      href="https://api.fontshare.com/v2/css?f[]=satoshi@1&amp;display=swap"
      rel="stylesheet" />
  </head>
  <body style="background-color:#f6f9fc">
    <div
      style="display:none;overflow:hidden;line-height:1px;opacity:0;max-height:0;max-width:0"
      data-skip-in-text="true">
      Holiday Request Approved
    </div>
    <table
      border="0"
      width="100%"
      cellpadding="0"
      cellspacing="0"
      role="presentation"
      align="center">
      <tbody>
        <tr>
          <td
            style='background-color:#f6f9fc;font-family:"Satoshi",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Ubuntu,sans-serif'>
            <table
              align="center"
              width="100%"
              border="0"
              cellpadding="0"
              cellspacing="0"
              role="presentation"
              style="max-width:37.5em;background-color:#ffffff;margin:0 auto;padding:0">
              <tbody>
                <tr style="width:100%">
                  <td>
                    <table
                      align="center"
                      width="100%"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="padding:10px 48px;background-color:#1E2B2E;text-align:center">
                      <tbody>
                        <tr>
                          <td>
                            @if($generalSetting && $generalSetting->site_logo)
                              <img
                                alt="{{ $generalSetting->site_title ?? 'Logo' }}"
                                height="50"
                                src="{{ asset($generalSetting->site_logo) }}"
                                style="display:block;outline:none;border:none;text-decoration:none" />
                            @else
                              <span style="color:#ffffff;font-size:24px;font-weight:bold;">{{ $generalSetting->site_title ?? config('app.name') }}</span>
                            @endif
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <p
                      style="font-size:18px;line-height:24px;font-weight:600;color:#1E2B2E;margin-bottom:20px;text-align:center;margin-top:16px">
                      Holiday Request Approved!
                    </p>
                    <table
                      align="center"
                      width="100%"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="background-color:#fffaf4;padding:0px 0">
                      <tbody>
                        <tr>
                          <td>
                            <div style="text-align:center;padding:40px 20px;">
                              <span style="font-size:48px;">🎉</span>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <table
                      align="center"
                      width="100%"
                      border="0"
                      cellpadding="0"
                      cellspacing="0"
                      role="presentation"
                      style="padding:40px 48px;border-bottom:5px solid #73BC1C">
                      <tbody>
                        <tr>
                          <td>
                            <p
                              style="font-size:16px;line-height:24px;font-weight:bold;color:#525f7f;margin-bottom:8px;margin-top:16px">
                              Congratulations {{ $user->name }},
                            </p>
                            <p
                              style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px">
                              Your holiday request has been approved. Please find the details about your approved holiday below:
                            </p>
                            <table
                              align="center"
                              width="100%"
                              border="0"
                              cellpadding="0"
                              cellspacing="0"
                              role="presentation"
                              style="background-color:#f8f8ef;padding:16px;border-radius:8px;margin-bottom:20px">
                              <tbody>
                                <tr>
                                  <td>
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >From Date:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >{{ $holiday->from_date->format($dateFormat) }}</span
                                      >
                                    </div>
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >To Date:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >{{ $holiday->to_date->format($dateFormat) }}</span
                                      >
                                    </div>
                                    @if($holiday->note)
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >Note:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >{{ $holiday->note }}</span
                                      >
                                    </div>
                                    @endif
                                    @if($holiday->recurring)
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >Recurring:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >Yes</span
                                      >
                                    </div>
                                    @endif
                                    @if($holiday->region)
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >Region:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >{{ $holiday->region }}</span
                                      >
                                    </div>
                                    @endif
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >Approval Date:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        >{{ $holiday->updated_at->format($dateFormat) }}</span
                                      >
                                    </div>
                                    <div
                                      style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;font-size:14px;line-height:20px">
                                      <span
                                        style="font-weight:600;color:#555;flex:0 0 40%"
                                        >Status:</span
                                      ><span
                                        style="text-align:right;flex:0 0 55%;color:#333"
                                        ><span
                                          style="background-color:#e6f7e9;color:#2b8a3e;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600"
                                          >● Approved</span
                                        ></span
                                      >
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                            <p
                              style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px">
                              This email was sent to you because your holiday request has been approved. If you did not request this holiday, please contact us immediately.
                            </p>
                            @if($generalSetting && ($generalSetting->email || $generalSetting->phone))
                            <p
                              style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px">
                              Please contact us @if($generalSetting->email)by mail at<!-- -->
                              <a
                                href="mailto:{{ $generalSetting->email }}"
                                style="color:#FF641A;text-decoration-line:none;text-decoration:underline"
                                target="_blank"
                                >{{ $generalSetting->email }}</a
                              >@endif @if($generalSetting->phone)<!-- -->or by phone at
                              <strong>{{ $generalSetting->phone }}</strong>@endif if you have
                              questions.
                            </p>
                            @endif
                            <p
                              style="font-size:15px;line-height:24px;color:#525f7f;margin-bottom:16px;text-align:left;margin-top:16px">
                              Best regards,<br />{{ $generalSetting->site_title ?? config('app.name') }}.
                            </p>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>

