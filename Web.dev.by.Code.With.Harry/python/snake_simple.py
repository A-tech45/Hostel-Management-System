import os
import time
import random
import sys
from collections import deque

class SimpleSnakeGame:
    def __init__(self):
        self.width = 20
        self.height = 10
        self.snake = deque([(10, 5)])
        self.direction = (1, 0)
        self.food = self.generate_food()
        self.score = 0
        self.game_over = False
        
    def generate_food(self):
        while True:
            food = (random.randint(0, self.width-1), random.randint(0, self.height-1))
            if food not in self.snake:
                return food
    
    def move_snake(self):
        if self.game_over:
            return
            
        head = self.snake[0]
        new_head = (head[0] + self.direction[0], head[1] + self.direction[1])
        
        # Check walls
        if new_head[0] < 0 or new_head[0] >= self.width or new_head[1] < 0 or new_head[1] >= self.height:
            self.game_over = True
            return
        
        # Check self collision
        if new_head in self.snake:
            self.game_over = True
            return
        
        self.snake.appendleft(new_head)
        
        # Check food
        if new_head == self.food:
            self.score += 10
            self.food = self.generate_food()
        else:
            self.snake.pop()
    
    def change_direction(self, key):
        if self.game_over:
            return
            
        directions = {
            'w': (0, -1), 'W': (0, -1),
            's': (0, 1), 'S': (0, 1),
            'a': (-1, 0), 'A': (-1, 0),
            'd': (1, 0), 'D': (1, 0),
            'up': (0, -1),
            'down': (0, 1),
            'left': (-1, 0),
            'right': (1, 0)
        }
        
        new_dir = directions.get(key)
        if new_dir and (new_dir[0] * -1, new_dir[1] * -1) != self.direction:
            self.direction = new_dir
    
    def draw(self):
        os.system('clear' if os.name == 'posix' else 'cls')
        
        print(f"Snake Game - Score: {self.score}")
        print("Controls: WASD or Arrow Keys, Q to quit")
        print("-" * (self.width + 2))
        
        for y in range(self.height):
            row = "|"
            for x in range(self.width):
                if (x, y) in self.snake:
                    if (x, y) == self.snake[0]:
                        row += "O"
                    else:
                        row += "o"
                elif (x, y) == self.food:
                    row += "*"
                else:
                    row += " "
            row += "|"
            print(row)
        
        print("-" * (self.width + 2))
        
        if self.game_over:
            print("GAME OVER! Press R to restart or Q to quit")
    
    def reset(self):
        self.__init__()
    
    def run(self):
        print("Snake Game Starting...")
        print("Use WASD or Arrow Keys to move")
        print("Press Q to quit, R to restart when game over")
        time.sleep(2)
        
        import threading
        import select
        
        running = True
        
        def get_input():
            nonlocal running
            while running:
                if select.select([sys.stdin], [], [], 0.1)[0]:
                    key = sys.stdin.readline().strip()
                    if key.lower() == 'q':
                        running = False
                    elif key.lower() == 'r' and self.game_over:
                        self.reset()
                    else:
                        self.change_direction(key)
        
        input_thread = threading.Thread(target=get_input)
        input_thread.daemon = True
        input_thread.start()
        
        while running:
            self.draw()
            self.move_snake()
            time.sleep(0.3)
        
        print("Game exited!")

if __name__ == "__main__":
    game = SimpleSnakeGame()
    game.run()
