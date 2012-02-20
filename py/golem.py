#!/usr/bin/env python3
import smtplib
from email.mime.text import MIMEText

botAdd = 'account@server.com'
fromAdd = 'Bot Name <%s>'%botAdd
toAdd = 'Your Name <account@server.com>'
password = 'very secret'

class Golem:
  
  def error(self, message):
    msg = MIMEText(message)
    msg['Subject'] = 'Error from ' + self.__class__.__name__
    msg['From'] = fromAdd
    msg['To'] = toAdd
    s = smtplib.SMTP_SSL('smtp.server.com', 465)
    s.login(botAdd, password)
    s.ehlo_or_helo_if_needed()
    s.sendmail(msg['From'], msg['To'].split(','), msg.as_string())
    s.quit()

