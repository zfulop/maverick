 

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
name                     | room type id | room type name                         |
11. Lemon                | 39           | Double room with shared bathroom       |
12. Yellow               | 39           | Double room with shared bathroom       |
13. The Blue Brothers    | 35           | 6 bed Dorm                             |
14. Mss Peach            | 35           | 6 bed Dorm                             |
15. Mr Green             | 42           | 10 bed dorm                            |
16. 4.em. Mia            | 46           | Double room ensuite                    |
18. 4.em. Jules          | 46           | Double room ensuite                    |
17. 4.em. Vincent        | 46           | Double room ensuite                    |
19. 4.em. Butch          | 46           | Double room ensuite                    |
20. 4.em. Honey          | 46           | Double room ensuite                    |
21. Nathan               | 69           | Deluxe Studio Apartment, Ferenciek     |
22. Simon                | 70           | Studio Apartment, Ferenciek            |
23. Kelly                | 72           | One-Bedroom Apartment 2.0, Ferenciek   |
24. Curtis               | 69           | Deluxe Studio Apartment, Ferenciek     |
25. Alisha               | 69           | Deluxe Studio Apartment, Ferenciek     |


 
 
Scenario 1: Get rooms

Given there are no virtual rooms

When I ask for the rooms from the API

Then the following rooms return from the API
room type name                          | name                            | type      | number of beds | id |
6 bed Dorm                              | 6 bed Dorm                      | DORM      | 6              | 35 |
Double room with shared bathroom        | Basic Double room               | PRIVATE   | 2              | 39 |
10 bed Dorm                             | 10 bed dorm                     | DORM      | 10             | 42 |
Double room ensuite                     | Standard Double room ensuite    | PRIVATE   | 2              | 46 |
Deluxe Studio Apartment, Ferenciek      | Deluxe Studio Apartment         | APARTMENT | 4              | 69 |
Studio Apartment, Ferenciek             | Studio Apartment                | APARTMENT | 2              | 70 | 
One-Bedroomm Apartment 2.0, Ferenciek   | One-Bedroom Apartment           | APARTMENT | 5              | 72 | 

 

Scenario 2: Get availability

Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |   4        |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |     X      |
22. Simon                | 70 Studio Apartment, Ferenciek          |    X       |    X       |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |     X      |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |    X       |            |

When I ask for the availability from the API
currency | from       | to         |
EUR      | 2010-01-02 | 2010-01-04 |

Then the following availability returns from the API
id | room type name                          | number of beds available | number of rooms available |
35 | 6 bed Dorm                              |     8                    |                           |
42 | 10 bed Dorm                             |    10                    |                           |
39 | Double room with shared bathroom        |                          | 2                         |
46 | Double room ensuite                     |                          | 5                         |
72 | One-Bedroomm Apartment 2.0, Ferenciek   |                          | 1                         |
69 | Deluxe Studio Apartment, Ferenciek      |                          | 1                         |
70 | Studio Apartment, Ferenciek             |                          | 0                         |





Scenario 3: Get availability with virtual rooms no bookings

Given there are no bookings
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
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |            |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |            |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When I ask for the availability from the API
currency | from       | to         |
EUR      | 2010-01-02 | 2010-01-04 |
 
Then the following availability returns from the API
id | room type name                          | number of beds available | number of rooms available |
35 | 6 bed Dorm                              | 12                       |                           |
42 | 10 bed Dorm                             | 10                       |                           |
39 | Double room with shared bathroom        |                          | 4                         |
46 | Double room ensuite                     |                          | 5                         |
72 | One-Bedroomm Apartment 2.0, Ferenciek   |                          | 2                         |
69 | Deluxe Studio Apartment, Ferenciek      |                          | 4                         |
70 | Studio Apartment, Ferenciek             |                          | 1                         |

 

 

Scenario 4: Get availability with virtual rooms there are some bookings
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms

Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |
 
Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |     2      |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |      X     |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |            |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |     X      |            |     X      |
20. 4.em. Honey          | 46 Double room ensuite                  |     X      |      X     |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
 
When I ask for the availability from the API
currency | from       | to         |
EUR      | 2010-01-02 | 2010-01-04 |

Then the following availability returns from the API
id | room type name                          | number of beds available | number of rooms available |
35 | 6 bed Dorm                              |     10                   |                           |
42 | 10 bed Dorm                             |     10                   |                           |
39 | Double room with shared bathroom        |                          | 3                         |
46 | Double room ensuite                     |                          | 2                         |
72 | One-Bedroomm Apartment 2.0, Ferenciek   |                          | 1                         |
69 | Deluxe Studio Apartment, Ferenciek      |                          | 4                         |
70 | Studio Apartment, Ferenciek             |                          | 1                         |
 




Scenario 5: Get availability with virtual rooms (only 1 room available so it does not count elsewhere)

Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms
 
Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |
 
Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 |
11. Lemon                | 39 Double room with shared bathroom     |            |            |            |
12. Yellow               | 39 Double room with shared bathroom     |            |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |    X       |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |    X       |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |    X       |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |    X       |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When I ask for the availability from the API
currency | from       | to         |
EUR      | 2010-01-02 | 2010-01-04 |
 
Then the following availability returns from the API
id | room type name                          | number of beds available | number of rooms available |
35 | 6 bed Dorm                              | 12                       |                           |
42 | 10 bed Dorm                             | 10                       |                           |
39 | Double room with shared bathroom        |                          | 2                         |
46 | Double room ensuite                     |                          | 1                         |
72 | One-Bedroomm Apartment 2.0, Ferenciek   |                          | 1                         |
69 | Deluxe Studio Apartment, Ferenciek      |                          | 2                         |
70 | Studio Apartment, Ferenciek             |                          | 1                         |
 
 
 
 
 
Scenario 6: Get availability with virtual rooms (only 1 room available but that is for a room with additional room type so its ok)

Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given there are no virtual rooms
 
Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
18. 4.em. Jules          | Double room with shared bathroom     |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |
 
Given the following bookings exist
room name                | room type                               | 2010-01-02 | 2010-01-03 | 2010-01-04 |
11. Lemon                | 39 Double room with shared bathroom     |    X       |            |            |
12. Yellow               | 39 Double room with shared bathroom     |    X       |            |            |
13. The Blue Brothers    | 35 6 bed Dorm                           |            |            |            |
14. Mss Peach            | 35 6 bed Dorm                           |            |            |            |
15. Mr Green             | 42 10 bed Dorm                          |            |            |            |
16. 4.em. Mia            | 46 Double room ensuite                  |            |            |            |
18. 4.em. Jules          | 46 Double room ensuite                  |            |            |            |
17. 4.em. Vincent        | 46 Double room ensuite                  |    X       |            |            |
19. 4.em. Butch          | 46 Double room ensuite                  |    X       |            |            |
20. 4.em. Honey          | 46 Double room ensuite                  |    X       |            |            |
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |     X      |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |     X      |            |            |
 
When I ask for the availability from the API
currency | from       | to         |
EUR      | 2010-01-02 | 2010-01-04 |
 
Then the following availability returns from the API
id | room type name                          | number of beds available | number of rooms available |
35 | 6 bed Dorm                              | 12                       | 2                         |
42 | 10 bed Dorm                             | 10                       | 1                         |
39 | Double room with shared bathroom        | 4                        | 2                         |
46 | Double room ensuite                     | 4                        | 2                         |
72 | One-Bedroomm Apartment 2.0, Ferenciek   | 2                        | 1                         |
69 | Deluxe Studio Apartment, Ferenciek      | 6                        | 1                         |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         |
