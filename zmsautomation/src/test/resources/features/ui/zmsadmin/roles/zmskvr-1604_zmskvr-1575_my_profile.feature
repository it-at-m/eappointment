#language: en
Feature: My profile shows only the LDAP name, the role and its permissions

	# ZMSKVR-1575 / ZMSKVR-1604.
	# Each role opens Mein Profil from the username in the header.
	# The page shows the LDAP name without @keycloak, the German role and the German permissions.
	# E-mail, changing the login and assigned units stay hidden.
	# Controlling signs in on the statistics site. That header opens the same profile.

	@web @zmsadmin @roles @clerk @technical-admin @ZMSKVR-1604 @ZMSKVR-1575 @executeLocally
	Scenario Outline: Each administration role opens a profile without extra fields
		When I open the administration website.
		Then I should be on the administration start page.
		When I sign in to the administration as "<account>" and wait for the header.
		And I open my profile from the header.
		Then my profile shows only the LDAP name, the role "<role>" and its permissions.

		Examples:
			| account           | role                        |
			| agent_basic       | Sachbearbeitung (Basis)     |
			| agent_queue       | Sachbearbeitung (Standard)  |
			| agent_queue_plus  | Sachbearbeitung (Erweitert) |
			| appointment_admin | Terminadministration        |
			| user_admin        | Benutzerverwaltung          |
			| audit_viewer      | Innenrevision               |
			| system_admin      | Technische Administration   |

	@web @zmsstatistic @controlling @ZMSKVR-1604 @ZMSKVR-1575 @executeLocally
	Scenario: Controlling opens a profile without extra fields
		When I open the statistics website.
		Then I should be on the statistics start page.
		When I sign in to the statistics as "reporting_viewer".
		And I open my profile from the header.
		Then my profile shows only the LDAP name, the role "Controlling" and its permissions.
