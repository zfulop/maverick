
Scenario 0: Preconditions

Verify that the following room types exist
id | name                                  | type      | number of beds | 
35 | 6 bed Dorm                            | DORM      | 6              | 
39 | Double room with shared bathroom      | PRIVATE   | 2              | 
42 | 10 bed Dorm                           | DORM      | 10             | 
46 | Double room ensuite                   | PRIVATE   | 2              | 
69 | Deluxe Studio Apartment, Ferenciek    | APARTMENT | 4              | 
70 | Studio Apartment, Ferenciek           | APARTMENT | 2              | 
72 | One-Bedroomm Apartment 2.0, Ferenciek | APARTMENT | 5              | 

Verify that the following rooms exist
id | name                     | room type id | room type name                         |
40 | 11. Lemon                | 39           | Double room with shared bathroom       |
39 | 12. Yellow               | 39           | Double room with shared bathroom       |
35 | 13. The Blue Brothers    | 35           | 6 bed Dorm                             |
36 | 14. Mss Peach            | 35           | 6 bed Dorm                             |
42 | 15. Mr Green             | 42           | 10 bed dorm                            |
46 | 16. 4.em. Mia            | 46           | Double room ensuite                    |
48 | 18. 4.em. Jules          | 46           | Double room ensuite                    |
49 | 17. 4.em. Vincent        | 46           | Double room ensuite                    |
50 | 19. 4.em. Butch          | 46           | Double room ensuite                    |
51 | 20. 4.em. Honey          | 46           | Double room ensuite                    |
80 | 21. Nathan               | 69           | Deluxe Studio Apartment, Ferenciek     |
82 | 22. Simon                | 70           | Studio Apartment, Ferenciek            |
79 | 23. Kelly                | 72           | One-Bedroom Apartment 2.0, Ferenciek   |
81 | 24. Curtis               | 69           | Deluxe Studio Apartment, Ferenciek     |
78 | 25. Alisha               | 69           | Deluxe Studio Apartment, Ferenciek     |




Scenario 1: Send availability to myalloc no virtual rooms
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |   4        |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |    X       |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |
22. Simon                | 70 Studio Apartment, Ferenciek          |    X       |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |    X       |

Given the following prices are set
id | room type                             | 2010-01-02 | 2010-01-03 |
39 | Double room with shared bathroom      |   60       |    65      |
35 | 6 bed Dorm                            |   12       |    16      |
42 | 10 bed Dorm                           |   10       |    14      |
46 | Double room ensuite                   |   70       |    78      |
69 | Deluxe Studio Apartment, Ferenciek    |  110       |   120      |
70 | Studio Apartment, Ferenciek           |  100       |   100      |
72 | One-Bedroomm Apartment 2.0, Ferenciek |   90       |    95      |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
room type name                           | price | date       | availability |
6 bed Dorm                               |   12  | 2010-01-02 |        8     |
10 bed Dorm                              |   10  | 2010-01-02 |       10     |
Double room with shared bathroom         |   60  | 2010-01-02 |        2     |
Double room ensuite                      |   70  | 2010-01-02 |        5     |
Deluxe Studio Apartment, Ferenciek       |  110  | 2010-01-02 |        2     |
Studio Apartment, Ferenciek              |  100  | 2010-01-02 |        1     |
6 bed Dorm                               |   16  | 2010-01-03 |       12     |
10 bed Dorm                              |   14  | 2010-01-03 |       10     |
Double room with shared bathroom         |   65  | 2010-01-03 |        2     |
Double room ensuite                      |   78  | 2010-01-03 |        4     |
Deluxe Studio Apartment, Ferenciek       |  120  | 2010-01-03 |        1     |
Studio Apartment, Ferenciek              |  100  | 2010-01-03 |        2     |





Scenario 2: Send availability to myallocator with virtual rooms no bookings
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following virtual rooms are configured
room name                | additional room type                  |
16. 4.em. Mia            | Double room with shared bathroom      |
16. 4.em. Mia            | One-Bedroomm Apartment 2.0, Ferenciek |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek    |
18. 4.em. Jules          | Double room with shared bathroom      |

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |

Given the following prices are set
id | room type                             | 2010-01-02 | 2010-01-03 |
39 | Double room with shared bathroom      |   60       |    65      |
35 | 6 bed Dorm                            |   12       |    16      |
42 | 10 bed Dorm                           |   10       |    14      |
46 | Double room ensuite                   |   70       |    78      |
69 | Deluxe Studio Apartment, Ferenciek    |  110       |   120      |
70 | Studio Apartment, Ferenciek           |  100       |   100      |
72 | One-Bedroomm Apartment 2.0, Ferenciek |   90       |    95      |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
room type name                           | price | date       | availability |
6 bed Dorm                               |   12  | 2010-01-02 |       12     |
10 bed Dorm                              |   10  | 2010-01-02 |       10     |
Double room with shared bathroom         |   60  | 2010-01-02 |        4     |
Double room ensuite                      |   70  | 2010-01-02 |        5     |
Deluxe Studio Apartment, Ferenciek       |  110  | 2010-01-02 |        4     |
Studio Apartment, Ferenciek              |  100  | 2010-01-02 |        3     |
6 bed Dorm                               |   16  | 2010-01-03 |       12     |
10 bed Dorm                              |   14  | 2010-01-03 |       10     |
Double room with shared bathroom         |   65  | 2010-01-03 |        4     |
Double room ensuite                      |   78  | 2010-01-03 |        5     |
Deluxe Studio Apartment, Ferenciek       |  120  | 2010-01-03 |        4     |
Studio Apartment, Ferenciek              |  100  | 2010-01-03 |        3     |







Scenario 3: Send availability to myallocator with virtual rooms there are some bookings

Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |     2      |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |      X     |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |     X      |            |
20. 4.em. Honey          | 46 Double room ensuite                  |     X      |      X     |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |

Given the following prices are set
id | room type                             | 2010-01-02 | 2010-01-03 |
39 | Double room with shared bathroom      |   60       |    65      |
35 | 6 bed Dorm                            |   12       |    16      |
42 | 10 bed Dorm                           |   10       |    14      |
46 | Double room ensuite                   |   70       |    78      |
69 | Deluxe Studio Apartment, Ferenciek    |  110       |   120      |
70 | Studio Apartment, Ferenciek           |  100       |   100      |
72 | One-Bedroomm Apartment 2.0, Ferenciek |   90       |    95      |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
room type name                           | price | date       | availability |
6 bed Dorm                               |   12  | 2010-01-02 |       10     |
10 bed Dorm                              |   10  | 2010-01-02 |       10     |
Double room with shared bathroom         |   60  | 2010-01-02 |        4     |
Double room ensuite                      |   70  | 2010-01-02 |        3     |
Deluxe Studio Apartment, Ferenciek       |  110  | 2010-01-02 |        4     |
Studio Apartment, Ferenciek              |  100  | 2010-01-02 |        2     |
6 bed Dorm                               |   16  | 2010-01-03 |       12     |
10 bed Dorm                              |   14  | 2010-01-03 |       10     |
Double room with shared bathroom         |   65  | 2010-01-03 |        3     |
Double room ensuite                      |   78  | 2010-01-03 |        3     |
Deluxe Studio Apartment, Ferenciek       |  120  | 2010-01-03 |        4     |
Studio Apartment, Ferenciek              |  100  | 2010-01-03 |        2     |





Scenario 4: Send availability to myallocator with virtual rooms (only 1 room available so it does not count elsewhere)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 
11. Lemon                | 39 Double room with shared bathroom     |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |    X       |      X     |
17. 4.em. Vincent        | 46 Double room ensuite                  |    X       |            |
19. 4.em. Butch          | 46 Double room ensuite                  |    X       |            |
20. 4.em. Honey          | 46 Double room ensuite                  |    X       |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |

Given the following prices are set
id | room type                             | 2010-01-02 | 2010-01-03 |
39 | Double room with shared bathroom      |   60       |    65      |
35 | 6 bed Dorm                            |   12       |    16      |
42 | 10 bed Dorm                           |   10       |    14      |
46 | Double room ensuite                   |   70       |    78      |
69 | Deluxe Studio Apartment, Ferenciek    |  110       |   120      |
70 | Studio Apartment, Ferenciek           |  100       |   100      |
72 | One-Bedroomm Apartment 2.0, Ferenciek |   90       |    95      |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
room type name                           | price | date       | availability |
6 bed Dorm                               |   12  | 2010-01-02 |       12     |
10 bed Dorm                              |   10  | 2010-01-02 |       10     |
Double room with shared bathroom         |   60  | 2010-01-02 |        2     |
Double room ensuite                      |   70  | 2010-01-02 |        1     |
Deluxe Studio Apartment, Ferenciek       |  110  | 2010-01-02 |        3     |
Studio Apartment, Ferenciek              |  100  | 2010-01-02 |        2     |
6 bed Dorm                               |   16  | 2010-01-03 |       12     |
10 bed Dorm                              |   14  | 2010-01-03 |       10     |
Double room with shared bathroom         |   65  | 2010-01-03 |        3     |
Double room ensuite                      |   78  | 2010-01-03 |        4     |
Deluxe Studio Apartment, Ferenciek       |  120  | 2010-01-03 |        4     |
Studio Apartment, Ferenciek              |  100  | 2010-01-03 |        2     |





Scenario 5: Send availability to myallocator with virtual rooms (only 1 room available but that is for a room with additional room type so its ok)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no prices set
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 |
11. Lemon                | 39 Double room with shared bathroom     |    X       |    X       |
12. Yellow               | 39 Double room with shared bathroom     |    X       |    X       |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |    X       |    X       |
19. 4.em. Butch          | 46 Double room ensuite                  |    X       |    X       |
20. 4.em. Honey          | 46 Double room ensuite                  |    X       |    X       |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |

Given the following prices are set
id | room type                             | 2010-01-02 | 2010-01-03 |
39 | Double room with shared bathroom      |   60       |    65      |
35 | 6 bed Dorm                            |   12       |    16      |
42 | 10 bed Dorm                           |   10       |    14      |
46 | Double room ensuite                   |   70       |    78      |
69 | Deluxe Studio Apartment, Ferenciek    |  110       |   120      |
70 | Studio Apartment, Ferenciek           |  100       |   100      |
72 | One-Bedroomm Apartment 2.0, Ferenciek |   90       |    95      |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
room type name                           | price | date       | availability |
6 bed Dorm                               |   12  | 2010-01-02 |       12     |
10 bed Dorm                              |   10  | 2010-01-02 |       10     |
Double room with shared bathroom         |   60  | 2010-01-02 |        2     |
Double room ensuite                      |   70  | 2010-01-02 |        2     |
Deluxe Studio Apartment, Ferenciek       |  110  | 2010-01-02 |        1     |
Studio Apartment, Ferenciek              |  100  | 2010-01-02 |        2     |
6 bed Dorm                               |   16  | 2010-01-03 |       12     |
10 bed Dorm                              |   14  | 2010-01-03 |       10     |
Double room with shared bathroom         |   65  | 2010-01-03 |        2     |
Double room ensuite                      |   78  | 2010-01-03 |        2     |
Deluxe Studio Apartment, Ferenciek       |  120  | 2010-01-03 |        2     |
Studio Apartment, Ferenciek              |  100  | 2010-01-03 |        2     |
