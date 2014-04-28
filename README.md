Ranked Ballot Engine
====================

A ranked ballot voting simulator that allows people to vote for candidates in a simulated election, then illustrate how the finally 50%+1 winner is tabulated.

Design
====================

Epiphany framework is used for REST routing and api handling.

All writes to the database will be done via POST with no response data provided. Where the POST is interactive users should be sent somewhere via 302 redirect.



