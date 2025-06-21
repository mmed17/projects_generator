// undone tasks per user 
SELECT 
	cards.*
FROM oc_deck_cards AS cards
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE 
	assignments.participant = 'admin' 
	AND 
	ISNULL(cards.done)
ORDER BY cards.created_at DESC;

// undone tasks per project
SELECT
	assignments.participant,
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 53   
    AND ISNULL(cards.done)
ORDER BY cards.created_at DESC;

// undone tasks per user in a project
SELECT
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 53                       
    AND assignments.participant = 'admin'      
    AND ISNULL(cards.done)                 
ORDER BY cards.created_at DESC;









// overdue tasks per user
SELECT 
	cards.*
FROM oc_deck_cards AS cards
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE 
	assignments.participant = 'admin' 
	AND ISNULL(cards.done) 
	AND cards.duedate <= CURRENT_TIMESTAMP()
ORDER BY cards.duedate DESC;

// overdue tasks per project
SELECT
	assignments.participant,
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 53    
    AND ISNULL(cards.done)
	AND cards.duedate <= CURRENT_TIMESTAMP()
ORDER BY cards.created_at DESC;

// overdue tasks per user per project
SELECT
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 53                       
    AND assignments.participant = 'admin'      
    AND ISNULL(cards.done)
	AND cards.duedate <= CURRENT_TIMESTAMP()
ORDER BY cards.created_at DESC;






// upcoming tasks per user
SELECT 
	cards.*
FROM oc_deck_cards AS cards
INNER JOIN oc_deck_assigned_users AS assignments 
ON cards.id = assignments.card_id
WHERE 
	assignments.participant = 'admin' AND 
	ISNULL(cards.done) AND 
	cards.duedate > CURRENT_TIMESTAMP()
ORDER BY cards.duedate DESC;

// upcoming tasks per project
SELECT
	assignments.participant,
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 54    
    AND ISNULL(cards.done)
	AND cards.duedate > CURRENT_TIMESTAMP()
ORDER BY cards.created_at DESC;

// upcoming tasks per user per project
SELECT
	cards.*
FROM oc_custom_projects AS projects
INNER JOIN oc_deck_stacks AS stacks ON stacks.board_id = projects.board_id
INNER JOIN oc_deck_cards  AS cards  ON cards.stack_id = stacks.id
INNER JOIN oc_deck_assigned_users AS assignments ON cards.id = assignments.card_id
WHERE
    projects.id = 54                       
    AND assignments.participant = 'admin'      
    AND ISNULL(cards.done)
	AND cards.duedate > CURRENT_TIMESTAMP()
ORDER BY cards.created_at DESC;