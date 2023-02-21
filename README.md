# plex-cast
Front end to add and remove members of a TV show's cast


IN metadata_items, in the column user_fields, CAST's id is 19

It'll look like 'lockedFields=1|2|3|4|5' - ALWAYS ADD 19 IN THERE OTHERWISE REFRESH METADATA WILL KILL IT

In tags, the tag_type for an actor is 6


The tables you want are tags, taggings, and metadata_items!
metadata_items is the movie, tags will have the actor, taggings is where you link em.



