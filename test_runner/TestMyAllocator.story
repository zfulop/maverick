
Scenario 0: Preconditions

Verify that the following room types exist
id | name                                 | type      | number of beds | 
35 | 6 bed Dorm                           | DORM      | 6              | 
39 | Double room with shared bathroom     | PRIVATE   | 2              | 
42 | 10 bed dorm                          | DORM      | 10             | 
46 | Double room ensuite                  | PRIVATE   | 2              | 
69 | Deluxe Studio Apartment, Ferenciek   | APARTMENT | 6              | 
70 | Studio Apartment, Ferenciek          | APARTMENT | 4              | 
72 | One-Bedroom Apartment 2.0, Ferenciek | APARTMENT | 2              | 

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




Scenario 1: Send availability to myalloc no virtual rooms
Given there are no bookings
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

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
id | room type name                          | number of beds available | number of rooms available | date       |
35 | 6 bed dorm                              | 8                        | 1                         | 2010-01-02 |
42 | 10 bed dorm                             | 10                       | 1                         | 2010-01-02 |
39 | Double room with shared bathroom        | 4                        | 2                         | 2010-01-02 |
46 | Double room ensuite                     | 10                       | 5                         | 2010-01-02 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2010-01-02 |
69 | Deluxe Studio Apartment, Ferenciek      | 12                       | 2                         | 2010-01-02 |
70 | Studio Apartment, Ferenciek             | 0                        | 0                         | 2010-01-02 |
35 | 6 bed dorm                              | 12                       | 2                         | 2010-01-03 |
42 | 10 bed dorm                             | 10                       | 1                         | 2010-01-03 |
39 | Double room with shared bathroom        | 4                        | 2                         | 2010-01-03 |
46 | Double room ensuite                     | 8                        | 4                         | 2010-01-03 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2010-01-03 |
69 | Deluxe Studio Apartment, Ferenciek      | 6                        | 1                         | 2010-01-03 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2010-01-03 |





Scenario 3: Send availability to myallocator with virtual rooms no bookings
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following virtual rooms are configured
room name                | additional room type                 |
16. 4.em. Mia            | Double room with shared bathroom     |
16. 4.em. Mia            | One-Bedroom Apartment 2.0, Ferenciek |
16. 4.em. Mia            | Deluxe Studio Apartment, Ferenciek   |
18. 4.em. Jules          | Double room with shared bathroom     |

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

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
id | room type name                          | number of beds available | number of rooms available | date       |
35 | 6 bed dorm                              | 12                       | 2                         | 2017-01-02 |
42 | 10 bed dorm                             | 10                       | 1                         | 2017-01-02 |
39 | Double room with shared bathroom        | 8                        | 4                         | 2017-01-02 |
46 | Double room ensuite                     | 10                       | 5                         | 2017-01-02 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 6                        | 3                         | 2017-01-02 |
69 | Deluxe Studio Apartment, Ferenciek      | 24                       | 4                         | 2017-01-02 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-02 |
35 | 6 bed dorm                              | 12                       | 2                         | 2017-01-03 |
42 | 10 bed dorm                             | 10                       | 1                         | 2017-01-03 |
39 | Double room with shared bathroom        | 8                        | 4                         | 2017-01-03 |
46 | Double room ensuite                     | 10                       | 5                         | 2017-01-03 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 6                        | 3                         | 2017-01-03 |
69 | Deluxe Studio Apartment, Ferenciek      | 24                       | 4                         | 2017-01-03 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-03 |




Scenario 4: Send availability to myallocator with virtual rooms there are some bookings
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

Given the following virtual rooms are configured
room name                | additional room type                    |
16. 4.em. Mia            | 39 Double room with shared bathroom     |
18. 4.em. Jules          | 39 Double room with shared bathroom     |
16. 4.em. Mia            | 69 Deluxe Studio Apartment, Ferenciek   |

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

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
id | room type name                          | number of beds available | number of rooms available | date       |
35 | 6 bed dorm                              | 10                       | 1                         | 2017-01-02 |
42 | 10 bed dorm                             | 10                       | 1                         | 2017-01-02 |
39 | Double room with shared bathroom        | 8                        | 4                         | 2017-01-02 |
46 | Double room ensuite                     | 6                        | 3                         | 2017-01-02 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2017-01-02 |
69 | Deluxe Studio Apartment, Ferenciek      | 24                       | 4                         | 2017-01-02 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-02 |
35 | 6 bed dorm                              | 10                       | 1                         | 2017-01-03 |
42 | 10 bed dorm                             | 12                       | 1                         | 2017-01-03 |
39 | Double room with shared bathroom        | 6                        | 3                         | 2017-01-03 |
46 | Double room ensuite                     | 6                        | 3                         | 2017-01-03 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2017-01-03 |
69 | Deluxe Studio Apartment, Ferenciek      | 24                       | 4                         | 2017-01-03 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-03 |





Scenario 5: Send availability to myallocator with virtual rooms (only 1 room available so it does not count elsewhere)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
id | room type name                          | number of beds available | number of rooms available | date
35 | 6 bed dorm                              | 12                       | 2                         | 2017
42 | 10 bed dorm                             | 10                       | 1                         |
39 | Double room with shared bathroom        | 4                        | 2                         |
46 | Double room ensuite                     | 2                        | 1                         |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         |
69 | Deluxe Studio Apartment, Ferenciek      | 12                       | 2                         |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         |





Scenario 6: Send availability to myallocator with virtual rooms (only 1 room available but that is for a room with additional room type so its ok)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |

When I update myallocator with the current availability
from       | to         |
2010-01-02 | 2010-01-03 |

Then the following availability is sent to myallocator
id | room type name                          | number of beds available | number of rooms available | date       |
35 | 6 bed dorm                              | 12                       | 2                         | 2017-01-02 |
42 | 10 bed dorm                             | 10                       | 1                         | 2017-01-02 |
39 | Double room with shared bathroom        | 4                        | 2                         | 2017-01-02 |
46 | Double room ensuite                     | 4                        | 2                         | 2017-01-02 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2017-01-02 |
69 | Deluxe Studio Apartment, Ferenciek      | 6                        | 1                         | 2017-01-02 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-02 |
35 | 6 bed dorm                              | 12                       | 2                         | 2017-01-02 |
42 | 10 bed dorm                             | 10                       | 1                         | 2017-01-02 |
39 | Double room with shared bathroom        | 4                        | 2                         | 2017-01-02 |
46 | Double room ensuite                     | 4                        | 2                         | 2017-01-02 |
72 | One-Bedroom Apartment 2.0, Ferenciek    | 2                        | 1                         | 2017-01-02 |
69 | Deluxe Studio Apartment, Ferenciek      | 6                        | 1                         | 2017-01-02 |
70 | Studio Apartment, Ferenciek             | 4                        | 1                         | 2017-01-02 |
