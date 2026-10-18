#language: en
Feature: Aufrufmonitor shows the newest called waiting number on top

	# ZMSALT-3250 covers ZMS-3213.
	# Newest calls insert at the top (left column, first row); older calls move down.
	# Seeding uses two clerks so both processes stay status=called.

	@web @zmscalldisplay @queue @ZMS-3213 @ZMSALT-3213 @ZMSALT-3250 @executeLocally
	Scenario: Newest called walk-in appears before the older call on the display
		Given the ZMS API is available
		When I prepare two called walk-ins at scope 2 with service "Zulassung Taxi oder Mietwagen" for call display order with the X-AuthKey
		And I open the call display for locations "2" with template "default_counter"
		Then the call display should be visible
		And the call display should list waiting numbers newest first for this scenario
		When I finish the called walk-ins prepared for call display order with the X-AuthKey
		And I delete the processes created in this scenario with the X-AuthKey
