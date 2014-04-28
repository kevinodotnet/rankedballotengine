Ranked Ballot Engine
====================

A ranked ballot voting simulator that allows people to vote for candidates in a simulated election, then illustrate how the finally 50%+1 winner is tabulated.

This is the code behind the voting simulator on www.ottawa123.ca (and perhaps other '123' websites).

Database
====================

The following database tables are used.

*sponsor* is a group or city that is holding mock elections. IE: ottawa123.ca. This is here just to make the entire site 'multi tenant' ready, so that
one hosted instance can support many electoral reform groups working independently in different cities.

*election* is held by a sponsor (they can hold multiple elections over time). Each election may have the same candidates again and again, or 
the candidates can be different.

*candidate* is someone who receives votes in an election. Name, biography and details are stored here, to be presented to electors while voting.

*elector* is someone who casts one or more votes in an election. This table likely only holds a COOKIE value to tie a browser instance to the person
who is voting. No fancy need for security here. These are mock elections!

*vote* is one vote for a candidate in a particular election. Electors can vote multiple times for different candidates. Either the 'rank' can
be assumed by 'lowest id' in the table or an explicit 'rank' column can be present. Electors should not have duplicate 'rank' votes in the same 
election (only one 1st vote, 2nd vote, etc)

Use Cases: TODO
====================

- add new sponsor city/group (backend only, rare)
- start new election (backend only, rare)
- add candidates (backend only, rare)

Add Elector / Votes
-----------------------

- When a member of the public comes in to vote in the election they should be presented with a single page and asked to choose their first pick.
- Record the vote.
- Continue until the user is done making choices (they do not need to pick all the way down).
- Electors should not need to pre-register. Upon casting their first vote they can be added to the elector table and then use an HTTP Cookie to
link their next vote to the first one.
- Like any voting system this should be as easy as possible. Complicated registrations will lower participation.

Show Election Outcome
------------------------

- One page that shows the result of the election in two modes: FPTP simulated, RANKED simple, RANKED complex.

*FPTP Simulated* shows who won the election if we only count 1st rankings.

*RANKED simple* shows who won after all the lowest ranked candidates are eliminated. It should list the candidates in reversed order of elimination.
(First candidate eliminated is shown last, second candidate eliminated shown second last, etc).

*RANKED complex* this is a multi-page wizard, one page per step in the ranked ballot calculation. 

- The first page shows the results of the first-ballot, click NEXT.
- The second page shows how the eliminated candidate's votes are redistributed (based on 2nd choice).
- The third page shows the results of the 2nd ballot, click NEXT. (same layout as the first ballot results, just different numbers).
- The fourth page shows how the eliminated candidate's (and all previously eliminated candidates') votes are now redistributed (based on 2nd, 3rd, etc choices).
- REPEAT until we're on the final ballot page where the winning candidate has achieved 50+1.
- Useful to show "nobody has won yet because X votes are needed to acheive 50+"
- Useful to show for each candidate row "needs X more votes to win".

NOTE
====

A single JSON api call will output all the data needed to produce the election results page (step by step through all ballots). Important to break
up the calculator-php from the view.


