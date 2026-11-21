<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Payment reminder</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7ef; color:#18332d; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#f4f7ef;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; overflow:hidden; border:1px solid #d3ded4; border-radius:18px; background-color:#ffffff;">
                    <tr>
                        <td style="padding:30px 32px; background-color:#173f35;">
                            <p style="margin:0 0 10px; color:#c9dac1; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Boarding House Payment Status System</p>
                            <h1 style="margin:0; color:#ffffff; font-size:26px; line-height:1.25;">Payment reminder</h1>
                            <p style="margin:9px 0 0; color:#efc86a; font-size:15px; font-weight:700;">{{ $monthYear }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 32px;">
                            <p style="margin:0 0 14px; font-size:16px; line-height:1.5;">Hello {{ $tenantName }},</p>
                            <p style="margin:0 0 22px; color:#53695d; font-size:14px; line-height:1.7;">Our records show an outstanding balance for this billing period. Here are the current details from your tenant account:</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; border:1px solid #dbe5dc; border-radius:12px; background-color:#f8faf5;">
                                <tr>
                                    <td style="padding:13px 16px; color:#718278; font-size:13px;">Billing period</td>
                                    <td align="right" style="padding:13px 16px; color:#31584b; font-size:13px; font-weight:700;">{{ $monthYear }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 16px; border-top:1px solid #e6ece5; color:#718278; font-size:13px;">Total amount due</td>
                                    <td align="right" style="padding:13px 16px; border-top:1px solid #e6ece5; color:#31584b; font-size:13px; font-weight:700;">₱{{ $totalDue }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 16px; border-top:1px solid #e6ece5; color:#718278; font-size:13px;">Confirmed amount paid</td>
                                    <td align="right" style="padding:13px 16px; border-top:1px solid #e6ece5; color:#32795b; font-size:13px; font-weight:700;">₱{{ $amountPaid }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:15px 16px; border-top:1px solid #e6ece5; color:#31584b; font-size:14px; font-weight:700;">Outstanding balance</td>
                                    <td align="right" style="padding:15px 16px; border-top:1px solid #e6ece5; color:#9a6418; font-size:18px; font-weight:800;">₱{{ $balance }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 16px; border-top:1px solid #e6ece5; color:#718278; font-size:13px;">Due date</td>
                                    <td align="right" style="padding:13px 16px; border-top:1px solid #e6ece5; color:#31584b; font-size:13px; font-weight:700;">{{ $dueDate }}</td>
                                </tr>
                            </table>

                            <p style="margin:22px 0; color:#53695d; font-size:13px; line-height:1.7;">If you have already submitted a payment, the amount will be applied after admin confirmation. You can check the latest status in your tenant portal.</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="border-radius:9px; background-color:#173f35;">
                                        <a href="{{ $dashboardUrl }}" style="display:inline-block; padding:13px 20px; border:1px solid #173f35; border-radius:9px; color:#ffffff; font-size:14px; font-weight:700; text-decoration:none;">View payment status</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; color:#718278; font-size:13px; line-height:1.6;">Thank you,<br><strong style="color:#31584b;">Boarding House Payment Status System</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:15px 32px; border-top:1px solid #edf1eb; background-color:#fbfcf9; color:#91a097; font-size:11px; line-height:1.5;">
                            This is an automated reminder for {{ $monthYear }}. Please contact the boarding-house admin if you have questions about your balance.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
