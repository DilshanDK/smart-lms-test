<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Exception;

class EncryptionService
{
    /**
     * Encrypt quiz questions with answers and correct answers
     */
    public static function encryptQuizData(array $questions): array
    {
        $encryptedQuestions = [];

        foreach ($questions as $index => $question) {
            try {
                // Encrypt the question text
                $encryptedQuestion = [
                    'question_encrypted' => Crypt::encryptString($question['question']),
                    'options_encrypted' => [],
                    'correct_answer_encrypted' => Crypt::encryptString((string)$question['correct_answer']),
                    'question_id' => 'q_' . ($index + 1) . '_' . bin2hex(random_bytes(8)) // Unique ID for each question
                ];

                // Encrypt each option
                foreach ($question['options'] as $optionIndex => $option) {
                    $encryptedQuestion['options_encrypted'][] = [
                        'option_encrypted' => Crypt::encryptString($option),
                        'option_id' => 'opt_' . $optionIndex . '_' . bin2hex(random_bytes(4))
                    ];
                }

                $encryptedQuestions[] = $encryptedQuestion;
            } catch (Exception $e) {
                throw new Exception('Failed to encrypt quiz data: ' . $e->getMessage());
            }
        }

        return $encryptedQuestions;
    }

    /**
     * Decrypt quiz questions for lecturer view (admin purposes)
     */
    public static function decryptQuizForLecturer(array $encryptedQuestions): array
    {
        $decryptedQuestions = [];

        foreach ($encryptedQuestions as $encQuestion) {
            try {
                $decryptedQuestion = [
                    'question' => Crypt::decryptString($encQuestion['question_encrypted']),
                    'options' => [],
                    'correct_answer' => (int)Crypt::decryptString($encQuestion['correct_answer_encrypted']),
                    'question_id' => $encQuestion['question_id']
                ];

                foreach ($encQuestion['options_encrypted'] as $encOption) {
                    $decryptedQuestion['options'][] = Crypt::decryptString($encOption['option_encrypted']);
                }

                $decryptedQuestions[] = $decryptedQuestion;
            } catch (Exception $e) {
                throw new Exception('Failed to decrypt quiz data: ' . $e->getMessage());
            }
        }

        return $decryptedQuestions;
    }

    /**
     * Decrypt only questions and options for student view (NO correct answers)
     */
    public static function decryptQuizForStudent(array $encryptedQuestions): array
    {
        $studentQuestions = [];

        foreach ($encryptedQuestions as $encQuestion) {
            try {
                $studentQuestion = [
                    'question' => Crypt::decryptString($encQuestion['question_encrypted']),
                    'options' => [],
                    'question_id' => $encQuestion['question_id']
                    // Notice: NO correct_answer field for students
                ];

                foreach ($encQuestion['options_encrypted'] as $encOption) {
                    $studentQuestion['options'][] = [
                        'text' => Crypt::decryptString($encOption['option_encrypted']),
                        'option_id' => $encOption['option_id']
                    ];
                }

                $studentQuestions[] = $studentQuestion;
            } catch (Exception $e) {
                throw new Exception('Failed to decrypt quiz for student: ' . $e->getMessage());
            }
        }

        return $studentQuestions;
    }

    /**
     * Validate student answers against encrypted correct answers
     */
    public static function validateStudentAnswers(array $encryptedQuestions, array $studentAnswers): array
    {
        $results = [];
        $correctCount = 0;

        foreach ($encryptedQuestions as $index => $encQuestion) {
            try {
                $correctAnswer = (int)Crypt::decryptString($encQuestion['correct_answer_encrypted']);
                $studentAnswer = isset($studentAnswers[$index]) ? (int)$studentAnswers[$index] : -1;

                $isCorrect = ($studentAnswer === $correctAnswer);
                if ($isCorrect) $correctCount++;

                $results[] = [
                    'question_id' => $encQuestion['question_id'],
                    'correct_answer' => $correctAnswer,
                    'student_answer' => $studentAnswer,
                    'is_correct' => $isCorrect
                ];
            } catch (Exception $e) {
                throw new Exception('Failed to validate answers: ' . $e->getMessage());
            }
        }

        return [
            'total_questions' => count($encryptedQuestions),
            'correct_answers' => $correctCount,
            'score_percentage' => count($encryptedQuestions) > 0 ? round(($correctCount / count($encryptedQuestions)) * 100, 2) : 0,
            'results' => $results
        ];
    }

    /**
     * Encrypt quiz data (questions array) - Simple base64 encoding
     */
    public static function encodeQuizData($questions)
    {
        // If already array, encode as JSON and then base64
        if (is_array($questions)) {
            $json = json_encode($questions);
            if ($json === false) {
                throw new \Exception('Failed to encode quiz data as JSON');
            }
            return base64_encode($json);
        }
        // If already string, just base64 encode
        return base64_encode((string)$questions);
    }

    /**
     * Decode quiz data (returns array of questions)
     */
    public static function decodeQuizData($encryptedData)
    {
        // If already array, convert MongoDB BSONArray to PHP array if needed
        if (is_array($encryptedData)) {
            return $encryptedData;
        }
        if ($encryptedData instanceof \MongoDB\Model\BSONArray) {
            return iterator_to_array($encryptedData, true);
        }
        if ($encryptedData instanceof \MongoDB\Model\BSONDocument) {
            return (array)$encryptedData;
        }
        // If null or empty, return empty array
        if (!$encryptedData) {
            return [];
        }
        // If string, decode base64 then json
        if (is_string($encryptedData)) {
            $decrypted = base64_decode($encryptedData, true);
            if ($decrypted === false) {
                throw new \Exception('Failed to base64 decode quiz data');
            }
            $data = json_decode($decrypted, true);
            if (!is_array($data)) {
                throw new \Exception('Failed to decode JSON after decryption');
            }
            return $data;
        }
        // If not recognized, return empty array
        return [];
    }

    /**
     * Encrypt an ID for use in URLs (Simple Base64)
     */
    public static function encryptId(string $id): string
    {
        // Simple base64 with URL-safe characters
        return rtrim(strtr(base64_encode($id), '+/', '-_'), '=');
    }

    /**
     * Decrypt an ID from URL (Simple Base64)
     */
    public static function decryptId(string $encryptedId): string
    {
        // Reverse the URL-safe base64
        $base64 = strtr($encryptedId, '-_', '+/');
        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            throw new Exception('Invalid encrypted ID format');
        }

        return $decoded;
    }
}
