$sendMailMessageSplat = @{
    From = 'User01 <user01@fabrikam.com>'
    To = 'User02 <user02@fabrikam.com>', 'User03 <user03@fabrikam.com>'
    Subject = 'Sending the Attachment'
    Body = "Forgot to send the attachment. Sending now."
    Attachments = '.\data.csv'
    Priority = 'High'
    DeliveryNotificationOption = 'OnSuccess', 'OnFailure'
    SmtpServer = 'smtp.fabrikam.com'
}
Send-MailMessage @sendMailMessageSplat