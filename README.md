Random Text
===========

A tool to create a random text variation of a base text, using elements from predefined arrays and from an input field.

Think: "David is a fairly average programmer." 
- "David" entered via input field
- "average" randomly picked from an array of adjectives 
- "programmer" randomly picked from an array of nouns
- Any number of text elements can be inserted randomly

Open index.php and...
- enter the specs of your MySql database
- enter the link to the google spreadsheet where you store the text elements (two columns: A = number of gap, B = text element)
- define your base text at the very bottom ($ret)

By default, five gaps are filled randomly, using the text elements defined in the spreadsheet. You can use any other number of gaps, just make sure to adjust the variables in line 136ff of index.php
