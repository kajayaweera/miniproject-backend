<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $modelName;
    private $responses;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.gemini.api_key');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $this->modelName = 'gemini-2.5-flash';

        // Fallback responses for when API is unavailable
        $this->responses = [
            
        ];
    }

    public function getResponse(string $userMessage, array $conversationHistory = []): string
    {
        // Try Gemini API first
        if ($this->apiKey) {
            try {
                return $this->generateChatResponse($userMessage, $conversationHistory);
            } catch (\Exception $e) {
                Log::error('Gemini API failed, falling back to static responses: ' . $e->getMessage());
            }
        }

        // Fallback to static responses
        return $this->getStaticResponse($userMessage);
    }

    private function generateChatResponse($userMessage, $conversationHistory = [])
    {
        try {
            $contents = $this->prepareConversationContents($userMessage, $conversationHistory);

            $url = $this->baseUrl . $this->modelName . ':generateContent?key=' . $this->apiKey;

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
                'tools' => [
                    [
                        'googleSearch' => (object)[]
                    ]
                ],
                'systemInstruction' => [
                    'parts' => [
                        [
                            'text' => 'You have access to Google Search for current information when needed.'
                        ]
                    ]
                ]
            ];

            $response = $this->client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return $responseData['candidates'][0]['content']['parts'][0]['text'];
            }

            return "I apologize, but I'm having trouble processing your request at the moment. Please try again.";

        } catch (RequestException $e) {
            Log::error('Gemini API Request Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function prepareConversationContents($userMessage, $conversationHistory = [])
    {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => ''
                    ]
                ]
            ],
            [
                'role' => 'model',
                'parts' => [
                    [
                        'text' => ''
                    ]
                ]
                    ],
             [
        // Example 1: Request for a classic bedtime story
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Can you tell me a bedtime story about a little bunny?'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Once upon a time, in a cozy meadow beneath the silver moon, there lived a little bunny named Cotton. Cotton had the softest white fur and the brightest curious eyes. Every night before bed, Cotton would hop to the hilltop to say goodnight to the stars. One special evening, a tiny star twinkled extra bright and whispered, "Sweet dreams, little Cotton." From that night on, Cotton knew that the stars were always watching over all the sleeping bunnies. And so, Cotton hopped back home, snuggled into the warm burrow, and drifted off to the most peaceful sleep. The end. Sweet dreams!'
        ]
    ]
],

// Example 2: Story with a specific theme
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Tell me a story about friendship'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'In a magical forest, there lived a shy little fox named Finn and a cheerful bird named Bella. Finn was afraid to make friends, but Bella sang the sweetest songs every morning. One day, Finn gathered courage and said, "Your songs make me happy." Bella chirped joyfully, "Then let\'s be friends!" Together, they played every day - Bella would fly high and tell Finn about the clouds, while Finn would share stories of the forest floor. They learned that friendship means being there for each other, no matter how different you are. That night, both friends fell asleep with smiles, knowing they\'d play together tomorrow. Goodnight, little one!'
        ]
    ]
],

// Example 3: Short story request
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'A quick story please, I\'m very sleepy'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'The little moon looked down and saw you getting ready for bed. "Sleep well, dear child," whispered the moon, wrapping you in soft, silvery light. The stars began their lullaby, and a gentle breeze tucked you in. Everything in the whole world was peaceful and safe. Sweet dreams, little sleepyhead. 🌙✨'
        ]
    ]
],

// Example 4: Story about overcoming fears
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'My child is afraid of the dark. Can you tell a story about that?'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Little Bear was afraid of the dark in his cave. One night, his mama said, "Let me show you something special." She took Little Bear outside, and he saw thousands of twinkling stars lighting up the night sky. "The dark isn\'t empty," Mama said gently. "It\'s full of beautiful lights, just waiting to shine for you." Little Bear also noticed the soft glow of fireflies dancing nearby and the gentle moonlight making everything silver and magical. From that night on, Little Bear wasn\'t afraid anymore. He knew the darkness was just a cozy blanket where all the pretty lights could sparkle. Now close your eyes, and imagine all those friendly lights watching over you. Sleep tight!'
        ]
    ]
],

// Example 5: Animal adventure story
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Tell me about a penguin adventure'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Pip the little penguin lived in Antarctica where everything sparkled with ice and snow. One day, Pip wondered what was beyond the big iceberg. With a brave heart, Pip waddled and slid across the ice. There, Pip found a family of seals playing in the water! They invited Pip to join their game of splash and swim. After a fun-filled day, Pip waddled home, tired but happy. Pip\'s mama was waiting with warm hugs. "You\'re so brave," she said. That night, Pip dreamed of new adventures, knowing home was always there waiting. Now it\'s time for you to rest too, little explorer. Goodnight!'
        ]
    ]
],

// Example 6: Magical story
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'A magical bedtime story with fairies'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'In the Whispering Woods, the Sleepy Fairy named Luna had a special job. Every night, she would sprinkle dream dust from her tiny pouch over all the sleeping children. The dust sparkled like tiny stars - gold for happy dreams, silver for peaceful sleep, and rainbow colors for magical adventures. Tonight, Luna flew to your window and gently sprinkled the most beautiful dream dust just for you. "Dream of wonderful things," she whispered with a smile. As Luna flew away, she left a trail of soft, twinkling light. Now close your eyes and let Luna\'s magic work. Sweet, magical dreams await you! ✨'
        ]
    ]
],

// Example 7: Gentle reassuring story
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'My child had a bad dream. Tell a comforting story'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'There once was a Dream Guardian named Mellow, a soft, fluffy cloud who floated above the world. Mellow\'s job was to catch any bad dreams in a special net and turn them into bubbles that floated away forever. Then Mellow would send down the gentlest, happiest dreams instead - dreams of playing in sunny meadows, hugging loved ones, and laughing with friends. Tonight, Mellow is floating right above your home, keeping watch with the kindest smile. No bad dreams can get through when Mellow is near. You are safe, loved, and protected. Rest now, dear one. Only sweet dreams will visit you tonight. 💙'
        ]
    ]
],

// Example 8: Nature-themed story
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Story about stars and moon'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'High up in the velvet sky, the Moon and her star children prepared for bedtime. "It\'s time for the world to sleep," said Mama Moon softly. Each little star found their special spot in the sky. The smallest star asked, "Mama, how do we help children sleep?" Mama Moon smiled warmly and said, "We shine our gentle light and send them peaceful thoughts. We remind them they\'re never alone." So all the stars twinkled their softest twinkle, and the Moon glowed her warmest glow. Down below, children everywhere felt safe and loved. The sky was watching over them, now and always. Look up and see them shining just for you. Goodnight, precious one. 🌙⭐'
        ]
    ]
],

// Example 9: Story with a lesson
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Tell a story about being kind'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Little Mouse found a hurt ladybug on a leaf. The ladybug\'s wing was bent and she couldn\'t fly home. Even though Mouse was small, she gently carried the ladybug all the way to her family. The ladybugs were so grateful! "Thank you for your kindness," they said. That night, something magical happened. The ladybugs came to Mouse\'s window and created a beautiful light show, blinking their tiny lights like stars. Mouse learned that even the smallest act of kindness creates the biggest, most wonderful magic. Your kind heart makes the world brighter too. Sleep well, sweet child. 🐞✨'
        ]
    ]
],

// Example 10: Very short goodnight
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Just say goodnight nicely'
        ]
    ]
],
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Tell me a short princess bedtime story'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Once upon a time, in a kingdom kissed by starlight, lived a brave princess named Aria. Every night she whispered kind wishes to the moon, and in return, the moon protected her dreams. One evening, a tiny golden firefly guided her to a secret garden where flowers glowed like gems. Aria made a wish for her kingdom to always stay peaceful, and the garden shimmered brighter. With a calm heart, she returned to her castle and fell asleep under the soft glow of moonlight. Goodnight, princess. 🌙✨'
        ]
    ]
],
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Say goodnight in a magical way'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Close your eyes and imagine the soft wings of stars wrapping you in a gentle hug. The night is calm, the world is quiet, and every dream is ready to welcome you. May your sleep be deep, warm, and filled with light. Goodnight, sweet soul. ✨💤'
        ]
    ]
],
[
    'role' => 'user',
    'parts' => [
        [
            'text' => 'Give me a conversation between a princess and a talking cat'
        ]
    ]
],
[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'Princess Elara: "Why do you always follow me, little cat?"  
Talking Cat: "Because someone needs to keep you out of trouble, Your Highness!"  
Princess Elara: "Trouble? Me?"  
Talking Cat: "Yes, you. Remember the dragon incident?"  
Princess Elara: *laughs* "Fine, stay with me then."  
Talking Cat: "I never planned to leave." 🐾👑'
        ]
    ]
],

[
    'role' => 'model',
    'parts' => [
        [
            'text' => 'The stars are twinkling, the moon is glowing, and all the world is peaceful and quiet. It\'s time to close your eyes and drift into the sweetest dreams. You are loved, you are safe, and tomorrow will be a beautiful day. Goodnight, sleep tight, and have the most wonderful dreams. 🌙💫'
        ]
    ]
]
    ],
    
        ];

        foreach ($conversationHistory as $message) {
            $contents[] = [
                'role' => $message['role'],
                'parts' => [
                    [
                        'text' => $message['content']
                    ]
                ]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $userMessage
                ]
            ]
        ];

        return $contents;
    }

    private function getStaticResponse(string $userMessage): string
    {
        $userMessage = strtolower(trim($userMessage));

        if ($this->containsGreeting($userMessage)) {
            return "Hello! i am your Story telling AI Agent";
        }

        foreach ($this->responses as $category => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (strpos($userMessage, strtolower($keyword)) !== false) {
                    return $data['response'];
                }
            }
        }

        return "Please contact Admins for more help that you";
    }

    private function containsGreeting(string $message): bool
    {
        $greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];

        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }

        return false;
    }
}
