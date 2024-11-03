DROP TABLE IF EXISTS user_ CASCADE;
DROP TABLE IF EXISTS administrator CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS saved_post CASCADE;
DROP TABLE IF EXISTS comment_ CASCADE;
DROP TABLE IF EXISTS reaction CASCADE;
DROP TABLE IF EXISTS friend_request CASCADE;
DROP TABLE IF EXISTS friendship CASCADE;
DROP TABLE IF EXISTS group_ CASCADE;
DROP TABLE IF EXISTS join_group_request CASCADE;
DROP TABLE IF EXISTS group_member CASCADE;
DROP TABLE IF EXISTS group_owner CASCADE;
DROP TABLE IF EXISTS notification CASCADE;
DROP TABLE IF EXISTS comment_notification CASCADE;
DROP TABLE IF EXISTS reaction_notification CASCADE;
DROP TABLE IF EXISTS friend_request_notification CASCADE;
DROP TABLE IF EXISTS group_request_notification CASCADE;
DROP TABLE IF EXISTS group_post_notification CASCADE;

DROP TYPE IF EXISTS statusGroup_request CASCADE;
DROP TYPE IF EXISTS statusFriendship_request CASCADE;
DROP TYPE IF EXISTS reactionType CASCADE;

-- Types

CREATE TYPE statusGroup_request AS ENUM ('requested', 'accepted', 'denied');
CREATE TYPE statusFriendship_request AS ENUM ('requested', 'accepted', 'denied');
CREATE TYPE reactionType AS ENUM ('like', 'laugh', 'cry', 'applause', 'shocked');

-- Tables

CREATE TABLE user_ (
    user_id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    email TEXT NOT NULL,
    profile_picture TEXT,
    user_password TEXT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE administrator (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id)
);

CREATE TABLE group_ (
    group_id SERIAL PRIMARY KEY,
    owner_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_name TEXT NOT NULL,
    description TEXT,
    visibility BOOLEAN NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE post (
    post_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT REFERENCES group_(group_id) ON UPDATE CASCADE,
    content TEXT,
    IMAGE1 TEXT,
    IMAGE2 TEXT,
    IMAGE3 TEXT,
    is_public BOOLEAN DEFAULT TRUE NOT NULL,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (content IS NOT NULL OR IMAGE1 IS NOT NULL)
);

CREATE TABLE saved_post (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE comment_ (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    comment_content TEXT NOT NULL,
    commentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reaction (
    reaction_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    reactionType reactionType NOT NULL,
    reaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE friend_request (
    request_id SERIAL PRIMARY KEY,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_status statusFriendship_request NOT NULL,
    sender_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    receiver_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE
);

CREATE TABLE friendship (
    user_id1 INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    user_id2 INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id1, user_id2)
);

CREATE TABLE join_group_request (
    request_id SERIAL PRIMARY KEY,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    request_status statusGroup_request NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE group_member (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE group_owner (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE notification (
    notification_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    related_id INT,
    is_read BOOLEAN DEFAULT false,
    notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comment_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    comment_id INT NOT NULL REFERENCES comment_(id) ON UPDATE CASCADE
);

CREATE TABLE reaction_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    reaction_id INT NOT NULL REFERENCES reaction(reaction_id) ON UPDATE CASCADE
);

CREATE TABLE friend_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    friend_request_id INT NOT NULL REFERENCES friend_request(request_id) ON UPDATE CASCADE
);

CREATE TABLE group_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    group_request_id INT NOT NULL REFERENCES join_group_request(request_id) ON UPDATE CASCADE
);

CREATE TABLE group_post_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE
);
