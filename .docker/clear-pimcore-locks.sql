-- Rescue script for local / Docker dev when elements stay "locked" in Studio.
-- Removes:
--   * tree_locks   — tree locks from Advanced → Lock (self / propagate)
--   * edit_lock    — short-lived locks while an element is opened for editing
--
-- Do NOT run this on production unless you understand the impact.

DELETE FROM tree_locks;
DELETE FROM edit_lock;
