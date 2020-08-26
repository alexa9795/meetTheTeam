configure DATABASE_URL from .env using db-user=root db-password=admin db-host=127.0.0.1 db-port=3306 db-name=coddict and set serverVersion=5.7;

create databese using php bin/console doctrine:schema:update --force;

create user using command php bin/console register-user -f FirstName,LastName -m email@gmail.com; if email is already registered in database, command output will print that email, 
else, a success message will display the new user's id; default password is set to 'default'

login user - access http://localhost:8000/ which will redirect to Login page http://localhost:8000/login; user user email and password to login; 

register user - access http://localhost:8000/registration to register new user in database (same functionality as bin/console command)

as a register user, I can see a list of my team meambers that I have previously added to my team; for each user, there is and edit and a delete option; i can add a new user using "+" 
from 'Add new team member' section, having default password set to 'default'

each registered user can update his password, accessing 'Reset password' link from top right corner;

user can log out, accessing 'Logout' link from top right corner and being redirected to Login page;


