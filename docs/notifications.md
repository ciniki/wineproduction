# Customer Notifications

The customer notifications is the queueing and sending of emails to 
customers based on actions that have occured on an order.

## Process Flow

Whenever an order is modified, the notificationTrigger is called which 
puts the notifications in the queue to be sent. These notifications
could be scheduled hours, days or weeks into the future.

The cron job checks for notifications in the queue and calls notificationQueueItemProcess.
This function checks on various conditions of the customer, order and other emails
sent to ensure multiple emails are bombarding the customer to quickly
with the same information.

Checks and balances are in place in both notificationTrigger and notificationQueueItemProcess.
