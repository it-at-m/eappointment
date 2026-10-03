#language: en
Feature: User administration requires a department for a new account

	# ZMSKVR-1497 / ZMSKVR-1624. Benutzerverwaltung (user_admin).
	# A new local account saved without a Behörde shows the input error,
	# asks for a department, and outlines that field in red.
	# No account is created.
	# The user list for this login answers 500, because the department search
	# cache key contains user_admin@keycloak. The scenario opens /users/add/ directly.

	@web @zmsadmin @user-administration @ZMSKVR-1624 @ZMSKVR-1497 @executeLocally
	Scenario: Saving a new account without a department shows an input error
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "user_admin" and wait for the header.
		And I open the form for a new user.
		And I prefer a local login for the new user.
		And I enter the new user "muster.zmskvr1624" with password "muster1624".
		And I save the new user.
		Then the new user form asks for a department and outlines Behörde in red.
