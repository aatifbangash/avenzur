So basically the run-integration.php file will receive a json then use services from services folder to connect to database and parse json then insert it

For now run-integration.php is not tested with actucal payload but tested with fake one with testWebhook.php

#TODO: 
    Add payload valdtion tests
    Add logging 
    Verify payload with database before inserting, if user exist update