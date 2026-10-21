#language: en
Feature: User administration rejects invalid usernames

	# ZMSKVR-533 / ZMSKVR-661.
	# New Sachbearbeiter accounts in zmsadmin take an LDAP-style login name.
	# Allowed: lowercase letters, digits, dot, hyphen (schema also allows underscore).
	# Forbidden: spaces, uppercase, other special characters (e.g. @ in the local part).
	# Default "Ja, keycloak als Provider nutzen" stays selected; the server appends @keycloak.
	# The signed-in account has the ZMS role user_admin (Benutzerverwaltung).
	# The user list answers 500, because the department search cache key contains
	# the login's @keycloak suffix. The scenario opens /users/add/ directly.

	@web @zmsadmin @user-administration @ZMSKVR-533 @ZMSKVR-661 @executeLocally
	Scenario Outline: Saving a new account with an invalid username shows the character restriction
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "user_admin" and wait for the header.
		And I open the form for a new user.
		Then the default Keycloak provider should be selected for the new user.
		When I enter the new user username "<username>".
		And I select the signed-in department for the new user.
		And I save the new user.
		Then the new user form shows the username character restriction.

		Examples:
			| username         | reason     |
			| user@example.de  | at-sign    |
			| user name        | space      |
			| UserName         | uppercase  |
