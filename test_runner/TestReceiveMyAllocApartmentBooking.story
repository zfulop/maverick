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




Scenario 1: Book 2 apartments at once
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |     X      |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type                          | start date   | end date    | currency | units | price  | comission |
                                   | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Deluxe Studio Apartment, Ferenciek | 2010-01-02   | 2010-01-03  |          | 1     | 80    | 0         |

Then the following bookings will exist in the db
room type                          | room                  | first night | last night | booking type | room payment |
Deluxe Studio Apartment, Ferenciek | 24. Curtis;25. Alisha | 2010-01-02  | 2010-01-03 | ROOM         | 80           |



Scenario 2: Book 2 apartments at once
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |     X      |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type                          | start date   | end date    | currency | units | price  | comission |
                                   | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Deluxe Studio Apartment, Ferenciek | 2010-01-02   | 2010-01-03  |          | 2     | 120    | 0         |

Then the following bookings will exist in the db
room type                          | room       | first night | last night | booking type | room payment |
Deluxe Studio Apartment, Ferenciek | 24. Curtis | 2010-01-02  | 2010-01-03 | ROOM         | 60           |
Deluxe Studio Apartment, Ferenciek | 25. Alisha | 2010-01-02  | 2010-01-03 | ROOM         | 60           |


Scenario 3: Book 2 apartments sparately
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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
21. Nathan               | 69 Deluxe Studio Apartment, Ferenciek   |    X       |    X       |     X      |
22. Simon                | 70 Studio Apartment, Ferenciek          |            |            |            |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type                          | start date   | end date    | currency | units | price  | comission |
                                   | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Deluxe Studio Apartment, Ferenciek | 2010-01-02   | 2010-01-03  |          | 1     | 100    | 0         |
Deluxe Studio Apartment, Ferenciek | 2010-01-02   | 2010-01-03  |          | 1     | 100    | 0         |

Then the following bookings will exist in the db
room type                          | room       | first night | last night | booking type | room payment |
Deluxe Studio Apartment, Ferenciek | 24. Curtis | 2010-01-02  | 2010-01-03 | ROOM         | 100          |
Deluxe Studio Apartment, Ferenciek | 25. Alisha | 2010-01-02  | 2010-01-03 | ROOM         | 100          |




Scenario 4: Book 1 apartment where a myalloc id is connected to 2 room types (room type ids: 70,72)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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
22. Simon                | 70 Studio Apartment, Ferenciek          |     X      |    X       |     X      |
23. Kelly                | 72 One-Bedroom Apartment 2.0, Ferenciek |            |            |            |
24. Curtis               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |
25. Alisha               | 69 Deluxe Studio Apartment, Ferenciek   |            |            |            |

When the following booking arrives from myallocator
room type                          | start date   | end date    | currency | units | price  | comission |
                                   | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Studio Apartment, Ferenciek        | 2010-01-02   | 2010-01-03  |          | 1     | 90     | 0         |

Then the following bookings will exist in the db
room type                             | room       | first night | last night | booking type | room payment |
One-Bedroomm Apartment 2.0, Ferenciek | 23. Kelly  | 2010-01-02  | 2010-01-03 | ROOM         | 90           |


Scenario 5: Book 2 apartments where a myalloc id is connected to 2 room types (room type ids: 70,72)
Given there are no bookings
Start date | end date
2010-01-01 | 2010-12-31

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

When the following booking arrives from myallocator
room type                          | start date   | end date    | currency | units | price  | comission |
                                   | 2010-01-02   | 2010-01-03  | EUR      |       |        |           |
Studio Apartment, Ferenciek        | 2010-01-02   | 2010-01-03  |          | 1     | 95     | 0         |
Studio Apartment, Ferenciek        | 2010-01-02   | 2010-01-03  |          | 1     | 95     | 0         |

Then the following bookings will exist in the db
room type                             | room       | first night | last night | booking type | room payment |
Studio Apartment, Ferenciek           | 22. Simon  | 2010-01-02  | 2010-01-03 | ROOM         | 95           |
One-Bedroomm Apartment 2.0, Ferenciek | 23. Kelly  | 2010-01-02  | 2010-01-03 | ROOM         | 95           |

